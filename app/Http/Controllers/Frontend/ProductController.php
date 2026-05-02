<?php
namespace App\Http\Controllers\Frontend;
use App\Models\User;
use App\Services\FilterService;
use Exception;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Traits\GoogleAnalytics4;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Resources\PickupInfoResource;
use Modules\Product\Entities\ProductReport;
use Modules\UserActivityLog\Traits\LogActivity;
use Modules\Product\Services\ReportReasonService;
use Modules\AuctionProducts\Entities\Auction;
use Modules\AuctionProducts\Entities\AuctionBid;
use Modules\AuctionProducts\Entities\AuctionEntryAmountPayment;
use Modules\CheckPincode\Entities\PinCodeConfigurations;
class ProductController extends Controller
{
    use GoogleAnalytics4;

    protected $productService, $reason,$filterService;
    public function __construct(ProductService $productService, ReportReasonService $reportReasonService,FilterService $filterService)
    {
        $this->productService = $productService;
        $this->reason = $reportReasonService;
        $this->filterService = $filterService;
        $this->middleware('maintenance_mode');
    }

    public function show($seller,$slug = null)
    {

        session()->forget('item_details');
        if($slug){
            $product =  $this->productService->getActiveSellerProductBySlug($slug, $seller);
        }else{
            $product =  $this->productService->getActiveSellerProductBySlug($seller);
        }
        if($product->status == 0 || $product->product->status == 0){
            return abort(404);
        }
        if (auth()->check()) {
            $this->productService->recentViewStore($product->id);
        } else {
            $recentViwedData = [];
            $recentViwedData['product_id'] = $product->id;
            if(session()->has('recent_viewed_products')){
                $recent_viewed_products = collect();
                foreach (session()->get('recent_viewed_products') as $key => $recentViwedItem){
                    $recent_viewed_products->push($recentViwedItem);
                }
                $recent_viewed_products->push($recentViwedData);
                session()->put('recent_viewed_products', $recent_viewed_products);
            }
            else{
                $recent_viewed_products = collect([$recentViwedData]);
                session()->put('recent_viewed_products', $recent_viewed_products);
            }
        }

        $this->productService->recentViewIncrease($product->id);
        $item_details = session()->get('item_details');
        $options = array();
        $data = array();
        if ($product->product->product_type == 2 && $product->variant_details != '') {
            $item_detail = [];
            foreach ($product->variant_details as $key => $v) {
                $item_detail[$key] = [
                    'name' => $v->name,
                    'attr_id' => $v->attr_id,
                    'code' => $v->code,
                    'value' => $v->value,
                    'id' => $v->attr_val_id,
                ];
                array_push($data, $v->value);
            }
            if (!empty($item_details)) {
                session()->put('item_details', $item_details + $item_detail);
            }else {
                session()->put('item_details', $item_detail);
            }
        }

        $reviews = $product->reviews->where('status',1)->pluck('rating');
        if(count($reviews)>0){
            $value = 0;
            $rating = 0;
            foreach($reviews as $review){
                $value += $review;
            }
            $rating = $value/count($reviews);
            $total_review = count($reviews);
        }else{
            $rating = 0;
            $total_review = 0;
        }
        //ga4
        if(app('business_settings')->where('type', 'google_analytics')->first()->status == 1){
            $eData = [
                'name' => 'view_item',
                'params' => [
                    "currency" => currencyCode(),
                    "value"=> 1,
                    "items" => [
                        [
                            "item_id"=> $product->product->skus[0]->sku,
                            "item_name"=> $product->product_name,
                            "currency"=> currencyCode(),
                            "price"=> $product->product->skus[0]->selling_price
                        ]
                    ],
                ],
            ];
            $this->postEvent($eData);
        }
        //end ga4
        $recent_viewed_products = $this->productService->recentViewedLast3Product($product->id);
        $reasons = $this->reason->get();
        // Attach auction context if this product is under an active auction
        $auction = null;
        $max_bid = null;
        $is_entry_amount_paid = 0;
        $hide_purchase_cta = false;
        $is_auction_product = false;
        try {
            $auction = Auction::where('seller_product_id', $product->id)
                ->where('status', 1)
                ->first();
            if ($auction) {
                $is_auction_product = true;
                $max_bid = AuctionBid::where('auction_id', $auction->id)->max('bid_amount');
                if (auth()->check()) {
                    $entryAmount = AuctionEntryAmountPayment::where('user_id', auth()->user()->id)
                        ->where('auction_id', $auction->id)
                        ->latest()
                        ->first();
                    if (!empty($entryAmount) && $entryAmount->status == 1) {
                        $is_entry_amount_paid = 1;
                    } elseif (!empty($entryAmount) && $entryAmount->status == 0) {
                        $is_entry_amount_paid = 2; // pending
                    } else {
                        $is_entry_amount_paid = 0; // not paid
                    }
                }

                // Hide purchase CTA if highest bid reaches configured percentage of product price
                try {
                    $percentage = isset($auction->percentage) ? floatval($auction->percentage) : 0.0;
                    if ($percentage <= 0) {
                        $percentage = 50.0; // default threshold if not configured
                    }
                    $baseSku = $product->skus->where('status', 1)->first();
                    $basePrice = $baseSku ? floatval($baseSku->selling_price) : (isset($product->product->skus[0]) ? floatval($product->product->skus[0]->selling_price) : 0.0);
                    if ($basePrice > 0 && $max_bid !== null) {
                        $threshold = ($basePrice * $percentage) / 100.0;
                        if (floatval($max_bid) >= $threshold) {
                            $hide_purchase_cta = true;
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore and do not hide cta
                }
            }
        } catch (\Throwable $e) {
            // fail soft; product page should still render without auction widgets
        }

        if(isModuleActive('CheckPincode')){
            $pincodeConfig = PinCodeConfigurations::first();
            return view(theme('pages.product_details'),compact('product','rating','total_review','recent_viewed_products','pincodeConfig','reasons','auction','max_bid','is_entry_amount_paid','hide_purchase_cta', 'is_auction_product'));
        }
        return view(theme('pages.product_details'),compact('product','rating','total_review','recent_viewed_products','reasons','auction','max_bid','is_entry_amount_paid','hide_purchase_cta', 'is_auction_product'));

    }
    public function showVendors(Request $request)
    {
        // Show all sellers (both Shops and Vendors)
        $sort_by = $request->get('sort_by');
        $paginate = (int) $request->get('paginate', 9);
        if ($paginate <= 0) { $paginate = 9; }

        $products = User::query()
            ->with('SellerAccount')
            ->whereHas('role', function ($q) { $q->where('name', 'Seller'); })
            ->join('seller_accounts', 'seller_accounts.user_id', '=', 'users.id')
            ->select('users.*');

        if ($sort_by === 'alpha_asc') {
            $products->orderBy('seller_accounts.seller_shop_display_name', 'asc');
        } elseif ($sort_by === 'alpha_desc') {
            $products->orderBy('seller_accounts.seller_shop_display_name', 'desc');
        } else {
            $products->orderBy('users.created_at', 'desc');
        }

        $data['products'] = $products->paginate($paginate);

        if ($request->ajax()) {
            return view(theme('partials.vendors_paginate_data'), $data);
        } else {
            $data['products']->appends($request->except('page'));
            return view(theme('pages.vendors'), $data);
        }
    }

    public function showShopVendors(Request $request, $seller)
    {
        // Accept seller slug or base64 user id
        $shop = null;
        try {
            $shop = User::where('slug', $seller)->first();
            if (!$shop) {
                $id = (int) base64_decode($seller);
                if ($id > 0) {
                    $shop = User::find($id);
                }
            }
        } catch (\Throwable $e) {
            $shop = null;
        }
        if (!$shop || !$shop->SellerAccount || $shop->SellerAccount->parent_seller_id !== null) {
            return abort(404);
        }

        $sort_by = $request->get('sort_by');
        $paginate = (int) $request->get('paginate', 9);
        if ($paginate <= 0) { $paginate = 9; }
        $products = User::query()
            ->with('SellerAccount')
            ->whereHas('role', function ($q) { $q->where('name', 'Seller'); })
            ->whereHas('SellerAccount', function ($q) use ($shop) { $q->where('parent_seller_id', $shop->id); })
            ->join('seller_accounts', 'seller_accounts.user_id', '=', 'users.id')
            ->select('users.*');

        if ($sort_by === 'alpha_asc') {
            $products->orderBy('seller_accounts.seller_shop_display_name', 'asc');
        } elseif ($sort_by === 'alpha_desc') {
            $products->orderBy('seller_accounts.seller_shop_display_name', 'desc');
        } else {
            $products->orderBy('users.created_at', 'desc');
        }
        $data['products'] = $products->paginate($paginate);
        $data['shop'] = $shop;

        if ($request->ajax()) {
            return view('frontend.amazy.partials.vendors_paginate_data', $data);
        } else {
            $data['products']->appends($request->except('page'));
            return view('frontend.amazy.pages.shop_vendors', $data);
        }
    }

    public function showShops(Request $request)
    {
        // Show only Shops (no parent) that have at least one Vendor
        $sort_by = $request->get('sort_by');
        $paginate = (int) $request->get('paginate', 9);
        if ($paginate <= 0) { $paginate = 9; }

        $products = User::query()
            ->with('SellerAccount')
            ->whereHas('role', function ($q) { $q->where('name', 'Seller'); })
            ->whereHas('SellerAccount', function ($q) { $q->whereNull('parent_seller_id'); })
            ->whereExists(function($q){
                $q->selectRaw('1')
                  ->from('seller_accounts as sa2')
                  ->whereColumn('sa2.parent_seller_id', 'users.id');
            })
            ->join('seller_accounts', 'seller_accounts.user_id', '=', 'users.id')
            ->select('users.*');

        if ($sort_by === 'alpha_asc') {
            $products->orderBy('seller_accounts.seller_shop_display_name', 'asc');
        } elseif ($sort_by === 'alpha_desc') {
            $products->orderBy('seller_accounts.seller_shop_display_name', 'desc');
        } else {
            $products->orderBy('users.created_at', 'desc');
        }

        $data['products'] = $products->paginate($paginate);

        if ($request->ajax()) {
            return view(theme('partials.stores_paginate_data'), $data);
        } else {
            $data['products']->appends($request->except('page'));
            return view(theme('pages.stores'), $data);
        }
    }
    public function show_in_modal(Request $request)
    {
        session()->forget('item_details');
        $product =  $this->productService->getProductByID($request->product_id);
        $this->productService->recentViewIncrease($request->product_id);
        $item_details = session()->get('item_details');
        $options = array();
        $data = array();
        if ($product->product->product_type == 2) {
            $item_detail = [];
            foreach ($product->variant_details as $key => $v) {
                $item_detail[$key] = [
                    'name' => $v->name,
                    'attr_id' => $v->attr_id,
                    'code' => $v->code,
                    'value' => $v->value,
                    'id' => $v->attr_val_id,
                ];
                array_push($data, $v->value);
            }

            if (!empty($item_details)) {
                session()->put('item_details', $item_details + $item_detail);
            } else{
                session()->put('item_details', $item_detail);
            }
        }
        $reviews = $product->reviews->where('status',1)->pluck('rating');
        if(count($reviews)>0){
            $value = 0;
            $rating = 0;
            foreach($reviews as $review){
                $value += $review;
            }
            $rating = $value/count($reviews);
            $total_review = count($reviews);
        }else{
            $rating = 0;
            $total_review = 0;
        }
        return (string) view(theme('partials.product_add_to_cart_modal'),compact('product','rating','total_review'));
    }

    public function admin_show_in_modal(Request $request)
    {
        session()->forget('item_details');
        $product =  $this->productService->getProductByID($request->product_id);
        $this->productService->recentViewIncrease($request->product_id);
        $item_details = session()->get('item_details');
        $options = array();
        $data = array();
        if ($product->product->product_type == 2) {
            foreach ($product->variant_details as $key => $v) {
                $item_detail[$key] = [
                    'name' => $v->name,
                    'attr_id' => $v->attr_id,
                    'code' => $v->code,
                    'value' => $v->value,
                    'id' => (int) $v->attr_val_id,
                ];
                array_push($data, $v->value);
            }

            if (!empty($item_details)) {
                session()->put('item_details', $item_details + $item_detail);
            } else{
                session()->put('item_details', $item_detail);
            }
        }
        $reviews = $product->reviews->where('status',1)->pluck('rating');
        if(count($reviews)>0){
            $value = 0;
            $rating = 0;
            foreach($reviews as $review){
                $value += $review;
            }
            $rating = $value/count($reviews);
            $total_review = count($reviews);
        }else{
            $rating = 0;
            $total_review = 0;
        }
        return view('backEnd.pages.customer_data.product_add_to_cart_modal',compact('product','rating','total_review'));
    }

    public function getReviewByPage(Request $request){
        $reviews = $this->productService->getReviewByPage($request->only('page', 'product_id'));
        $product = $this->productService->getProductByID($request->product_id);
        if($product){
            $all_reviews = $product->reviews;
        }else{
            $all_reviews = collect();
        }
        return view(theme('partials._product_review_with_paginate'),compact('reviews','all_reviews'));
    }

    public function getPickupByCity(Request $request){
        $get_pickup_location_by_city = $this->productService->getPickupByCity($request->except('_token'));
        return $get_pickup_location_by_city;
    }

    public function getPickupInfo(Request $request){



        $pickup = $this->productService->getPickupById($request->except('_token'));
        $shipping_method = $this->productService->getLowestShippingFromSeller($request->except('_token'));

//        dd($pickup);
        return response()->json([
            'pickup_location' => new  PickupInfoResource($pickup),
            'shipping' => $shipping_method
        ]);
    }

    public function submitReport(Request $request)
    {
        $data = $request->validate([
            "reason_id" => "nullable",
            "email" => "required",
            "comment" => "required",
            "product_id" => "required"
        ]);
       try{
            $create =  ProductReport::create($data);
            if($create){
                Toastr::success('product_reported','Success');
            }else{
                Toastr::error('Something went wrong','Error');
            }
            return back();
       }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.operation_failed'));
            return response()->json([
            "status" => 0,
        ]);
       }
    }


}
