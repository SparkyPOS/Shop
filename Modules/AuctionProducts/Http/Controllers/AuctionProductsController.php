<?php

namespace Modules\AuctionProducts\Http\Controllers;

use stdClass;
use Exception;
use App\Models\User;
use App\Traits\SendMail;
use App\Traits\ImageStore;
use Illuminate\Http\Request;
use App\Services\FilterService;
use App\Services\ProductService;
use App\Traits\GoogleAnalytics4;
use Modules\Setup\Entities\City;
use App\Services\CheckoutService;
use Modules\Setup\Entities\State;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Setup\Entities\Country;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Modules\Product\Entities\Category;
use Illuminate\Support\Facades\Artisan;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Modules\Seller\Entities\SellerProduct;
use Illuminate\Contracts\Support\Renderable;
use Modules\AuctionProducts\Entities\Auction;
use Modules\AuctionProducts\Events\NewBidEvent;
use Modules\UserActivityLog\Traits\LogActivity;
use Modules\AuctionProducts\Entities\AuctionBid;
use Modules\Wallet\Repositories\WalletRepository;
use Modules\GeneralSetting\Entities\EmailTemplate;
use Modules\Bkash\Http\Controllers\BkashController;
use Modules\GeneralSetting\Entities\GeneralSetting;
use Modules\AuctionProducts\Services\AuctionService;
use Modules\Product\Repositories\CategoryRepository;
use Modules\Product\Repositories\AttributeRepository;
use Modules\Clickpay\Http\Controllers\ClickpayController;
use Modules\PaymentGateway\Http\Controllers\PaytmController;
use Modules\PaymentGateway\Http\Controllers\PayPalController;
use Modules\PaymentGateway\Http\Controllers\StripeController;
use Modules\AuctionProducts\Entities\AuctionEntryAmountPayment;
use Modules\MercadoPago\Http\Controllers\MercadoPagoController;
use Modules\PaymentGateway\Http\Controllers\MidtransController;
use Modules\PaymentGateway\Http\Controllers\PaystackController;
use Modules\PaymentGateway\Http\Controllers\RazorpayController;
use Modules\PaymentGateway\Http\Controllers\InstamojoController;
use Modules\PaymentGateway\Http\Controllers\PayUmoneyController;
use Modules\PaymentGateway\Http\Controllers\BankPaymentController;
use Modules\PaymentGateway\Http\Controllers\FlutterwaveController;
use Modules\AuctionProducts\Entities\AuctionEntryAmountGatewayInfo;

class AuctionProductsController extends Controller
{
    use SendMail, ImageStore, GoogleAnalytics4;

    private $auctionService;
    private $productService;
    protected $filterService;
    protected $checkoutService;

    public function __construct(AuctionService $auctionService, ProductService $productService, FilterService $filterService,CheckoutService $checkoutService)
    {
        $this->middleware('maintenance_mode');
        $this->auctionService = $auctionService;
        $this->productService = $productService;
        $this->filterService = $filterService;
        $this->checkoutService = $checkoutService;
    }

    public function index()
    {
        return view('auctionproducts::index');
    }

    public function getData()
    {
        $user = auth()->user();

        // if(isset($_GET['table'])){
        //     $products = $this->productService->getFilterdProduct($_GET['table']);
        //     $status_slider = '_'.$_GET['table'].'_';
        // }else{
            // if($user->role->type == 'superadmin' || $user->role->type == 'admin' || $user->role->type == 'staff'){
        $auctions = $this->auctionService->getAuctions($user->role->type);
            // }
            // else if($user->role->type == 'seller'){
            //     $products = $this->productService->getSellerProduct();
            // }else{
            //     $products = $this->productService->getProduct();
            // }

        // }

        $type = $user->role->type;
        return DataTables::of($auctions)
            ->addIndexColumn()
            ->editColumn('auction_title', function ($auctions) {
                return $auctions->auction_title ?? '';
            })
            ->editColumn('product_name', function ($auctions) {
                return $auctions->seller_product->product_name ?? '';
            })
            ->addColumn('owner', function ($auctions) {
                return $auctions->seller->name ?? '';
            })
            ->addColumn('bid_starting_amount', function ($auctions) {
                return getNumberTranslate($auctions->starting_bidding_price ?? 0);
            })
            ->addColumn('auction_start_date', function ($auctions) {
                return getNumberTranslate($auctions->auction_start_date ?? '');
            })
            ->addColumn('auction_end_date', function ($auctions) {
                return getNumberTranslate($auctions->auction_end_date ?? '');
            })
            ->addColumn('total_bids', function ($auctions) {
                return getNumberTranslate($auctions->total_bids ?? 0);
            })
            ->addColumn('status', function ($auctions) {
                return getNumberTranslate($auctions->status==1 ? 'Active' : 'Inactive');
            })
            ->addColumn('action', function ($auctions) {
                return view('auctionproducts::components._projectAuctionAction', compact('auctions'));
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create()
    {
        try{
            $sellerProducts = $this->auctionService->getSellerProduct();
            return view('auctionproducts::auction_create', compact('sellerProducts'));
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'), __('common.error'));
            return back();
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'auction_title' => 'required',
            'seller_product_id' => 'required',
            'quantity' => 'required',
            'date' => 'required',
            'starting_bidding_price' => 'required',
            'auction_description' => 'required',
            'entry_amount' => "nullable",
            'increment_price' => "nullable",
            'reserve_price' => "required",
            'percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try{
            $this->auctionService->storeAuction($request->except('_token'));
            DB::commit();
            Toastr::success(__('common.created_successfully'), __('common.success'));
            LogActivity::successLog('Auction Created Successfully.');
            return redirect(route('auctionproducts.auction-product'));
        }catch(Exception $e){

            DB::rollBack();
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'), __('common.error'));
            return back();
        }
    }


    public function show($id)
    {
        return view('auctionproducts::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        // dd($id);
        try{
            $sellerProducts = $this->auctionService->getSellerProduct();
            $auction = $this->auctionService->getAuctionById($id);
            // dd($auction);
            return view('auctionproducts::auction_edit', compact('sellerProducts','auction'));
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'), __('common.error'));
            return back();
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request)
    {
        $request->validate([
            'auction_title' => 'required',
            'seller_product_id' => 'required',
            'quantity' => 'required',
            'date' => 'required',
            'starting_bidding_price' => 'required',
            'auction_description' => 'required',
            'entry_amount' => "nullable",
            'increment_price' => "required",
            'reserve_price' => "required",
            'percentage' => 'nullable|numeric|min:0|max:100',
            // 'status' => 'required'
        ]);

        DB::beginTransaction();
        try{
            $auction  = Auction::where('id',$request->id)->first();

            $this->auctionService->update($request->except('_token'));
            DB::commit();
            if($auction->auction_end_date > $request->end_date){
                 $admin = User::where('role_id',1)->first();
                 $emails[]= $admin->email;
                 $auctionUsers = AuctionBid::where('auction_id',$auction->id)->groupBy('user_id')->with(['user'])->get();
                 foreach($auctionUsers as $us)
                 {

                    if(!empty($us->customer_email))
                    {
                        $emails[] = $us->customer_email;
                    }
                 }
                 $this->sendMailToUsers($auction->id, 'auction_relist_template', $emails);
            }

            Toastr::success(__('common.updated_successfully'), __('common.success'));
            LogActivity::successLog('Auction Updated Successfully.');
            return redirect(route('auctionproducts.auction-product'));
        }catch(Exception $e){
            DB::rollBack();
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'), __('common.error'));
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Request $request)
    {
        try{
            $this->auctionService->destroy($request->id);
            LogActivity::successLog('Auction delete Successfully.');
            // return redirect(route('auctionproducts.auction-product'));
            return $this->loadTableData();
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
        }
    }

    public function viewAllBids($id){
        return view('auctionproducts::view_all_bids',compact('id'));
    }

    public function getViewAllBidsData($id){

        $bids = $this->auctionService->getViewAllBidsData($id);

        return DataTables::of($bids)
            ->addIndexColumn()
            ->editColumn('customer_name', function ($bids) {
                return $bids->customer_name ?? '';
            })
            ->addColumn('email', function ($bids) {
                return $bids->customer_email ?? '';
            })
            ->addColumn('phone', function ($bids) {
                return $bids->customer_phone ?? '';
            })
            ->addColumn('reserve_price',function($bids){
                $auction = Auction::find($bids->auction_id);
                return !empty($auction) ? $auction->reserve_price:0;
            })
            ->addColumn('bid_count', function ($bids) {
                return $bids->bid_count ?? '';
            })
            ->addColumn('bid_amount', function ($bids) {
                return getNumberTranslate($bids->bid_amount ?? 0);
            })
            ->addColumn('cancel_order', function ($bids) {
                return getNumberTranslate($bids->cancel_order==0 ? 'No' : 'Yes');
            })
            ->addColumn('date', function ($bids) {
                return getNumberTranslate($bids->created_at ?? '');
            })
            ->addColumn('action', function ($bids) {
                $auction = Auction::find($bids->auction_id);
                return view('auctionproducts::components._projectBidAction', compact('bids','auction'));
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function destroyThisBid($id){
        try{
            $this->auctionService->destroyThisBid($id);
            LogActivity::successLog('Bid delete Successfully.');
            Toastr::success(__('common.deleted_successfully'), __('common.success'));
            return redirect(route('auctionproducts.auction-product'));
        }catch(Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'));
        }
    }

    public function viewProduct($id,$seller_product_id)
    {

        $product =  $this->auctionService->getActiveSellerProductById($seller_product_id);
        $auction = $this->auctionService->getAuctionById($id);
        $is_entry_amount_paid = 1;

        $max_bid = $this->auctionService->maxBidAmount($id);
        return view('auctionproducts::product_details',compact('product','auction','is_entry_amount_paid','max_bid'));
    }

    public function payEntryAmount($id)
    {
        $auction = $this->auctionService->getAuctionById($id);
        Toastr::info('Auction entry fee is disabled. You can place a bid directly.', 'Info');
        return redirect()->route('auctionproducts.view', [$auction->id, $auction->seller_product_id]);
    }

    public function auctionEntryAmountPay(Request $request, $id)
    {
        $auction = $this->auctionService->getAuctionById($id);
        Toastr::info('Auction entry fee is disabled. You can place a bid directly.', 'Info');
        return redirect()->route('auctionproducts.view', [$auction->id, $auction->seller_product_id]);
    }

    public function placeBid(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'auction_id' => 'required|exists:auctions,id',
            'bid_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()->toArray(),
                'success' => 0
            ]);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => 4,
                'data' => 'login_required'
            ], 401);
        }

        $auction = Auction::where('id', $request->auction_id)
            ->where('status', 1)
            ->first();

        if (!$auction) {
            return response()->json([
                'success' => 5,
                'data' => 'auction_not_available'
            ]);
        }

        $today = Carbon::today();
        $startsAt = $auction->auction_start_date ? Carbon::parse($auction->auction_start_date)->startOfDay() : null;
        $endsAt = $auction->auction_end_date ? Carbon::parse($auction->auction_end_date)->endOfDay() : null;

        if (($startsAt && $today->lt($startsAt)) || ($endsAt && $today->gt($endsAt))) {
            return response()->json([
                'success' => 6,
                'data' => 'auction_not_active'
            ]);
        }

        if(isset($request->bid_amount)){
            $bidAmount = (float) $request->bid_amount;
            $increment = (float) ($auction->increment_price ?? 0);
            $maxBid = AuctionBid::where('auction_id',$request->auction_id)->max('bid_amount');
            if(!empty($maxBid)){
                $minimumBid = (float) $maxBid + max($increment, 0.01);
                if($bidAmount < $minimumBid){
                    return response()->json([
                        'success' => 3,
                        'data' => 'low_bid_amount',
                        'minimum_bid' => $minimumBid,
                    ]);
                }
            }else{
                $minimumBid = (float) ($auction->starting_bidding_price ?? 0);
                if($bidAmount < $minimumBid){
                    return response()->json([
                        'success' => 2,
                        'data' => 'first_low_bid',
                        'minimum_bid' => $minimumBid,
                    ]);
                }
            }
        }

        DB::beginTransaction();
        try{

            $response = $this->auctionService->savePlaceBid($request->except('_token'),$user->id);
            DB::commit();

            LogActivity::successLog('Bid Placed Successfully.');



            $event = [
                'id' => $auction->id,
                "message" => "New Bid has been placed on ".$auction->auction_title,
            ];
            event(new NewBidEvent($event));
            $admin = User::where('role_id',1)->first();
            $emails[]= $admin->email;
            $auctionUsers = AuctionBid::where('auction_id',$auction->id)->groupBy('user_id')->with(['user'])->get();
            foreach($auctionUsers as $us)
            {

               if(!empty($us->customer_email))
               {
                   $emails[] = $us->customer_email;
               }
            }

            $this->sendMailToUsers($auction->id, 'auction_new_bid', $emails);

            return response()->json([
                'success' => 1,
                'data' => $response
            ]);
        }catch(Exception $e){
            DB::rollBack();
            LogActivity::errorLog($e->getMessage());
            // Toastr::error(__('common.error_message'), __('common.error'));
            return back();
        }
    }

    public function settings($id)
    {
        $auction = Auction::findOrFail($id);
        return view('auctionproducts::auction_settings',compact('auction'));
    }

    public function auctionConfiguration()
    {
        $setting = GeneralSetting::where('id',1)->first();
        return view('auctionproducts::auction_configuration',compact('setting'));
    }

    public function updateSettings(Request $request)
    {
        DB::beginTransaction();
        try{
            $this->auctionService->updateAuctionSettings($request->except('_token'));
            DB::commit();
            Toastr::success(__('common.updated_successfully'), __('common.success'));
            LogActivity::successLog('Auction Settings Updated Successfully.');
            return redirect(route('auctionproducts.auction-product'));
        }catch(Exception $e){
            DB::rollBack();
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'), __('common.error'));
            return back();
        }
    }

    public function cronjob(){
        try {
            Artisan::call('command:auctionendcheck');
            return response()->json([
                'msg' => 'success'
            ],200);
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json([
                'msg' => 'error'
            ],500);
        }
    }

    public function emailBidderForAward(Request $request)
    {
        $bid_id = $request->bid_id;
        $auction_id = $request->auction_id;
        $auction_bid = AuctionBid::findOrFail($bid_id);
        $userId = $auction_bid->user_id;

        $auction = Auction::where('id',$auction_id)->first();
     // $general_setting = DB::table('general_settings')->select('mail_signature','mail_footer','site_title')->first();

        //new code
       $tamplate = EmailTemplate::where('type_id', 44)->where('is_active', 1)->first();
       $subject= $tamplate->subject;
       $body = $tamplate->value;

       $url = '<a href="'.route('auctionproducts.get.awarded.user.confirmation',[$auction_id,$bid_id,$userId]).'" class="btn btn-primary btn-lg">Confirm Order</a>';
       $array['subject'] = $subject;
       $array['from'] = env('SENDER_MAIL');
       $array['content'] = $body;

       $array['content'] = str_replace('{USER_FIRST_NAME}',$auction_bid->customer_name,$array['content']);
       $array['content'] = str_replace('{VERIFICATION_LINK}',$url,$array['content']);
       $array['content'] = str_replace('{EMAIL_SIGNATURE}',app('general_setting')->mail_signature,$array['content']);
       $array['content'] = str_replace('{EMAIL_FOOTER}',app('general_setting')->mail_footer,$array['content']);
       $mailPath = '\App\Mail\AuctionBidderAwardMail';
       $template = '/backEnd/template';
       $this->sendMailWithTemplate($auction_bid->customer_email,$array,$mailPath,$template);
       //end

       $emails = [];

       if($auction->reserve_price > $auction_bid->bid_amount){
            $admin = User::where('role_id',1)->first();
            $emails[]= $admin->email;
            $this->sendMailToUsers($auction->id, 'reserve_failed', [$emails]);
       }
       $auctionUsers = AuctionBid::where('auction_id',$auction->id)->groupBy('user_id')->with(['user'])->get();
       foreach($auctionUsers as $us)
       {

            if(!empty($us->customer_email))
            {
                $emails[] = $us->customer_email;
            }
        }
       //$this->sendMailToUsers($auction->id, 'auction_finished_template', $emails);
       Toastr::success(__('auctionproduct.mail_send_successfully'), __('common.success'));
       LogActivity::successLog('Auction Settings Updated Successfully.');
       return redirect(route('auctionproducts.view.all.bids',$auction_id));
    }

    public function getUserOrderConfirmationPage($auction_id, $bid_id, $user_id)
    {
        if(Auth::check() && Auth::user()->id==$user_id){
            return view('frontend.amazy.pages.bidder_confirmation',compact('auction_id','bid_id','user_id'));
        }else{
            return redirect()->route('login');
        }
    }

    public function cancelAuctionOrder($auction_id,$bid_id)
    {
        // dd($auction_id,$bid_id);
        $bid = $this->auctionService->cancelAuctionOrder($bid_id);
        $auction = $this->auctionService->getAuctionById($auction_id);
        // Email to admin
        $tamplate = EmailTemplate::where('type_id', 46)->where('is_active', 1)->first();
        $subject= $tamplate->subject;
        $body = $tamplate->value;

        $general_setting = DB::table('general_settings')->select('mail_signature','mail_footer','site_title')->first();
        $url = '<a href="'.route('auctionproducts.auction-product').'" class="btn btn-primary btn-lg">Watch Auction</a>';
        $array['subject'] = $subject;
        $array['from'] = env('SENDER_MAIL');
        $array['content'] = $body;
        $array['content'] = str_replace('{EMAIL_SIGNATURE}',$general_setting->mail_signature,$array['content']);
        $array['content'] = str_replace('{EMAIL_FOOTER}',$general_setting->mail_footer,$array['content']);
        $array['content'] = str_replace('{VERIFICATION_LINK}',$url,$array['content']);
        $array["content"] = str_replace("{USER_FIRST_NAME}", $auction->seller->first_name, $array["content"]);
        $array["content"] = str_replace("{WEBSITE_NAME}", $general_setting->site_title, $array["content"]);
        $array["content"] = str_replace("{CUSTOMER_NAME}", $bid->customer_name, $array["content"]);
        $array["content"] = str_replace("{BIDDING_AMOUNT}", $bid->bid_amount, $array["content"]);
        $mailPath = '\App\Mail\AuctionOrderCancelMail';
        $template = '/backEnd/template';
        $this->sendMailWithTemplate($auction->seller->email,$array,$mailPath,$template);

        Toastr::success(__('auctionproduct.order_cancelled_successfully'), __('common.success'));
        LogActivity::successLog('Auction Order Cancelled Successfully.');
        return redirect(route('frontend.welcome'));
    }


    public function sendMailToUsers($auction_id, $mail_type, $emails)
    {
        $template_type = DB::table('email_template_types')->where('type',$mail_type)->first();

        $auction = $this->auctionService->getAuctionById($auction_id);
        if($template_type){
            $tamplate = EmailTemplate::where('type_id', $template_type->id)->where('is_active', 1)->first();

            $subject= $tamplate->subject;
            $body = $tamplate->value;

            $general_setting = DB::table('general_settings')->select('mail_signature','mail_footer','site_title')->first();
            $url = '<a href="'.route('auctionproducts.auction-product').'" class="btn btn-primary btn-lg">Watch Auction</a>';
            $array['subject'] = $subject;
            $array['from'] = env('SENDER_MAIL');
            $array['content'] = $body;
            $array['content'] = str_replace('{EMAIL_SIGNATURE}',$general_setting->mail_signature,$array['content']);
            $array['content'] = str_replace('{AUCTION_TITLE}',$general_setting->mail_signature,$array['content']);
            $array['content'] = str_replace('{EMAIL_FOOTER}',$general_setting->mail_footer,$array['content']);
            $array['content'] = str_replace('{VERIFICATION_LINK}',$url,$array['content']);
            $array["content"] = str_replace("{USER_FIRST_NAME}", $auction->seller->first_name, $array["content"]);
            $array["content"] = str_replace("{WEBSITE_NAME}", $general_setting->site_title, $array["content"]);

            $mailPath = '\App\Mail\AuctionSendMail';
            $template = '/backEnd/template';
            foreach($emails as $email){
                $this->sendMailWithTemplate($email,$array,$mailPath,$template);
            }

        }


    }

    public function confirmAuctionOrder($auction_id,$bid_id)
    {
        $auction = Auction::with('seller_product','seller','auction_bid')->where('id',$auction_id)->first();
        $awarded_bid = AuctionBid::findOrFail($bid_id);
        $slug = !empty($auction->seller_product->slug) ? $auction->seller_product->slug : null;
        $seller = $auction->seller->slug;


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
        return view(theme('pages.auction_product_details'),compact('product','rating','total_review','recent_viewed_products','auction','awarded_bid'));

    }

    public function getSellerProductByAjax(Request $request){
        $products = $this->auctionService->getSellerProductByAjax($request->search);
        return response()->json($products);
    }

    public function getAllProduct()
    {
        $data['products'] = $this->auctionService->getAllAuctionProduct(null, null);
        $catRepo = new CategoryRepository(new Category());
        $data['CategoryList'] = $catRepo->activeCategory();
        $attributeRepo = new AttributeRepository;
        $data['attributeLists'] = $attributeRepo->getActiveAllWithoutColor()->where('status', 1);
        $data['color'] = $attributeRepo->getColorAttr();
        if (session()->has('filterDataFromCat')) {
            session()->forget('filterDataFromCat');
        }
        $product_min_price = $this->filterService->filterProductMinPrice();
        $product_max_price = $this->filterService->filterProductMaxPrice();
        $product_min_price = $this->filterService->getConvertedMin($product_min_price);
        $product_max_price = $this->filterService->getConvertedMax($product_max_price);
        $data['min_price_lowest'] = $product_min_price;
        $data['max_price_highest'] = $product_max_price;
        return view('auctionproducts::auction_product_gallary', $data);
    }


    public function auctionHistory($id)
    {
        try{
            $auction = $this->auctionService->getAuctionById($id);
            $bids = AuctionBid::where('auction_id',$id)
                            ->orderBy('bid_amount','DESC')
                            ->with(['user'])
                            ->get();
            $view = view('auctionproducts::bid_history',compact('bids','auction'))->render();
            return response()->json([
                "html" => $view,
                "status" => 1
            ],200);
        }catch(Exception $e)
        {
            return response()->json([
                "html" => "",
                "staus" => 0
            ],404);
        }
    }

    public function entryAmounts()
    {
        return view('auctionproducts::entry_amount');
    }

    public function entryAmountData()
    {
        if(auth()->user()->role_id == 1){
            $payments = AuctionEntryAmountPayment::with(['customer','paymentMethod','auction']);
        }else{
            $payments = AuctionEntryAmountPayment::whereHas('auction',function($query){
                $query->where('user_id',auth()->id());
            })->with(['customer','paymentMethod','auction']);
        }

        return DataTables::of($payments)
            ->addIndexColumn()
            ->addColumn('customer', function ($payment) {
                return $payment->customer->last_name;
            })
            ->addColumn('auction', function ($payment) {
                return $payment->auction->auction_title;
            })

            ->addColumn('paymentMethod', function ($payment) {

                return $payment->paymentMethod->method;
            })
            ->addColumn('status', function ($payment) {
                return view('auctionproducts::components._entry_payment_status',compact('payment'));
            })
            ->addColumn('action', function ($payment) {
                return view('auctionproducts::components._entry_amount_action', compact('payment'));
            })
            ->rawColumns(['action'])
            ->toJson();
    }


    public function entryAmountDetails($id)
    {
       try{
            $payment =  AuctionEntryAmountPayment::with(['customer','paymentMethod','auction','paymentInfo'])->where('id',$id)->first();
            $view = view('auctionproducts::components._entry_amount_details',compact('payment'))->render();
            return response()->json([
                "html" => $view,
                "status" => 1
            ],200);
       }catch(Exception $e){
            return response()->json([
                "status" => 0,
                "msg" => trans('common.Something Went Wrong','Error')
            ],501);
       }
    }

    public function entryAmountChangeStatus(Request $request,$id)
    {
        try{
            $data = $request->all();
            AuctionEntryAmountPayment::with(['customer','paymentMethod','auction','paymentInfo'])->where('id',$id)->update($data);
            Toastr::success(trans('common.operation_done_successfully'),trans('common.error'));
            return back();
        }catch(Exception $e){
            Toastr::error(trans('common.operation_failed'),trans('common.error'));
            return back();
        }
    }


    public function auctionConfigurationuUpdate(Request $request)
    {
        $data = $request->all();
        try{
            $settings = GeneralSetting::where('id',1)->first();

            if($settings)
            {
                $settings->update([
                    "pusher_key" => isset($data['pusher_key']) ? $data['pusher_key']:'',
                    "pusher_secret" => isset($data['pusher_secret']) ? $data['pusher_secret']:'',
                    "pusher_cluster" => isset($data['pusher_cluster']) ? $data['pusher_cluster']:'',
                    "pusher_app_id" => isset($data['pusher_app_id']) ? $data['pusher_app_id']:'',
                ]);

                if(isset($data['pusher_app_id'])){
                    putEnvConfigration('PUSHER_APP_ID',$data['pusher_app_id']);
                }


                if(isset($data['pusher_key'])){
                    putEnvConfigration('PUSHER_APP_KEY',$data['pusher_key']);
                    putEnvConfigration('BROADCAST_DRIVER','pusher');
                }

                if(isset($data['pusher_secret'])){
                    putEnvConfigration('PUSHER_APP_SECRET',$data['pusher_secret']);
                }

                if(isset($data['pusher_cluster'])){
                    putEnvConfigration('PUSHER_APP_CLUSTER',$data['pusher_cluster']);
                }


                Toastr::success(trans('common.operation_done_successfully'),trans('common.success'));
                return back();
            }
            Toastr::error(trans('common.operation_failed'),trans('common.error'));
            return back();
        }catch(Exception $e){
            Toastr::error(trans('common.operation_failed'),trans('common.error'));
            return back();
        }
    }
}
