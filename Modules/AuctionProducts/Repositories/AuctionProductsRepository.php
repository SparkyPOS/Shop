<?php

namespace Modules\AuctionProducts\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Product\Entities\Product;
use Modules\Seller\Entities\SellerProduct;

use Modules\AuctionProducts\Entities\Auction;
use Modules\AuctionProducts\Entities\AuctionBid;

class AuctionProductsRepository
{
    public function getAuctions($userType){
        if($userType=='superadmin' || $userType=='admin' || $userType=='staff'){
            return Auction::with('seller_product')->get();
        }else{
            return Auction::with('seller_product')->where('user_id',auth()->user()->id)->get();
        }
    }

    public function getSellerProduct()
    {
        $user = auth()->user();
        if ($user->role->type == 'superadmin' || $user->role->type == 'admin' || $user->role->type == 'staff') {
            return SellerProduct::with('product', 'seller.role')->whereHas('product', function($query){
                $query->where('product_type',1);
            })->activeSeller()->get();
        } elseif ($user->role->type == 'seller') {
            return SellerProduct::with('product', 'seller.role')->where('user_id', $user->id)->whereHas('product', function($query){
                $query->where('product_type',1);
            })->activeSeller()->get();
        } else {
            return [];
        }
    }

    public function storeAuction($request)
    {
        $start_date = date('Y-m-d', strtotime($request['start_date']));
        $end_date = date('Y-m-d', strtotime($request['end_date']));

        $user = auth()->user();
        Auction::create([
            'user_id' => $user->id,
            'auction_title' => $request['auction_title'],
            'seller_product_id' => $request['seller_product_id'],
            'quantity' => $request['quantity'],
            'auction_start_date' => $start_date,
            'auction_end_date' => $end_date,
            'starting_bidding_price' => $request['starting_bidding_price'],
            'auction_description' => $request['auction_description'],
            'status' => isset($request['status']) ? $request['status'] : 0,
            "reserve_price" => isset($request['reserve_price']) ? $request['reserve_price']:0,
            "entry_amount" => isset($request['entry_amount']) ? $request['entry_amount']:0,
            "increment_price" => isset($request['increment_price']) ? $request['increment_price']:0,
        ]);
    }

    public function getAuctionById($id){
        return Auction::with('seller_product','seller')->findOrFail($id);
    }

    public function maxBidAmount($id)
    {
        return AuctionBid::where('auction_id',$id)->max('bid_amount');
    }

    public function update($request){
        $auction = Auction::findOrFail($request['id']);
        if($auction != null){
            $start_date = date('Y-m-d', strtotime($request['start_date']));
            $end_date = date('Y-m-d', strtotime($request['end_date']));

            $auction->update([
                'auction_title' => $request['auction_title'],
                'seller_product_id' => $request['seller_product_id'],
                'quantity' => $request['quantity'],
                'auction_start_date' => $start_date,
                'auction_end_date' => $end_date,
                'starting_bidding_price' => $request['starting_bidding_price'],
                'auction_description' => $request['auction_description'],
                'status' => isset($request['status']) ? $request['status'] : 0,
                "reserve_price" => isset($request['reserve_price']) ? $request['reserve_price']:0,
                "entry_amount" => isset($request['entry_amount']) ? $request['entry_amount']:0,
                "increment_price" => isset($request['increment_price']) ? $request['increment_price']:0,
            ]);
        }
    }

    public function destroy($id){
        $auction = Auction::findOrFail($id);
        $auction->delete();
    }

    public function destroyThisBid($id){
        $auctionBid = AuctionBid::findOrFail($id);
        $auctionBid->delete();
    }

    public function getActiveSellerProductById($seller_product_id)
    {
        return SellerProduct::where('id', $seller_product_id)->with('product.tags','related_sales.related_seller_products.seller','cross_sales.cross_seller_products.seller','up_sales.up_seller_products.seller', 'skus','seller')->activeSeller()->firstOrFail();

    }

    public function savePlaceBid($request,$user_id)
    {
        $user = Auth::user();
        $auction = new AuctionBid();
        $auction->auction_id = $request['auction_id'];
        $auction->user_id = $user_id;
        $auction->customer_name = $user->first_name;
        $auction->customer_email = $user->email;
        $auction->customer_phone = $user->mobile_verified_at;
        $auction->bid_amount = $request['bid_amount'];
        if($auction->save()){
            return true;
        }else{
            return false;
        }
    }

    public function updateAuctionSettings($request)
    {
        $auction = Auction::findOrFail($request['id']);
        $auction->update([
            'auction_end_date' => date('Y-m-d',strtotime($request['auction_end_date'])),
            'bidder_award_system' => $request['bidder_award_system']
        ]);
    }

    public function cancelAuctionOrder($bid_id)
    {
        $bid = AuctionBid::findOrFail($bid_id);
        $bid->update([
            'cancel_order' => 1
        ]);
        return $bid;
    }

    public function getViewAllBidsData($id)
    {
        $bids = DB::table('auction_bids')
        ->select(DB::raw('MAX(id) as id'),'auction_id','user_id','customer_name',
        'customer_email','customer_phone','is_send','confirm_order','cancel_order',
        'created_at',DB::raw('count(user_id) as bid_count'),

        DB::raw('MAX(bid_amount) as bid_amount'))
        ->orderBy('bid_amount','desc')
        ->groupBy('user_id')
        ->where('auction_id',$id)
        ->get();
        return $bids;
    }

    public function getAllAuctionProduct($sort_by, $paginate)
    {
        $products = SellerProduct::with('skus', 'product')->activeSeller()->select('seller_products.*','auctions.id as auction_id','auctions.seller_product_id','auctions.quantity','auctions.starting_bidding_price','auctions.auction_start_date','auctions.auction_end_date','auctions.auction_title')->join('products', function ($query) {
                $query->on('products.id','=','seller_products.product_id')->where('products.status', 1);
            })
            ->join('auctions', function ($query) {
                $query->on('auctions.seller_product_id','=','seller_products.id')->where('auctions.status', 1);
            })
            ->distinct('seller_products.id');

        return $this->sortAndPaginate($products, $sort_by, $paginate);
    }

    public function sortAndPaginate($products, $sort_by, $paginate_by)
    {
        $sort = 'desc';
            $column = 'created_at';
            if(in_array($sort_by,['old','alpha_asc','low_to_high'])){
                $sort = 'asc';
            }
            if(in_array($sort_by,['alpha_asc','alpha_desc'])){
                $column = 'product_name';
            }
            elseif ($sort_by == "low_to_high") {
                $column = 'min_sell_price';
            }
            elseif ($sort_by == "high_to_low") {
                $column = 'max_sell_price';
            }
        if(get_class($products) == \Illuminate\Database\Eloquent\Builder::class){
            $products = $products->orderBy($column, $sort);
        }else{
            if($sort == 'asc'){
                $products = $products->sortBy($column);
            }else{
                $products = $products->sortByDesc($column);
            }
        }
        return $products->paginate(($paginate_by != null) ? $paginate_by : 9);
    }

    public function getSellerProductByAjax($search){
        $products = collect();
        $user = getParentSeller();
        if($search != ''){
            if($user->role->type == 'superadmin'){
                $products = SellerProduct::with('product', 'seller.role')->where('product_name', 'LIKE', "%{$search}%")
                            ->whereHas('product', function($query){
                                $query->where('product_type',1);
                            })
                            ->activeSeller()->paginate(10);
            }
            elseif($user->role->type == 'seller'){
                $products = SellerProduct::with('product', 'seller.role')->where('product_name', 'LIKE', "%{$search}%")->where('user_id',$user->id)
                            ->whereHas('product', function($query){
                                $query->where('product_type',1);
                            })
                            ->activeSeller()->paginate(10);
            }
        }else{
            if($user->role->type == 'superadmin'){
                $products = SellerProduct::with('product', 'seller.role')
                            ->whereHas('product', function($query){
                                $query->where('product_type',1);
                            })
                            ->activeSeller()->paginate(10);
            }
            elseif($user->role->type == 'seller'){
                $products = SellerProduct::with('product', 'seller.role')->where('user_id',$user->id)
                            ->whereHas('product', function($query){
                                $query->where('product_type',1);
                            })
                            ->activeSeller()->paginate(10);
            }
        }
        $response = [];
        foreach($products as $product){
            if(isModuleActive('MultiVendor')){
                $text = '';
                if($product->seller->role->type == 'seller'){
                    $text = $product->seller->first_name;
                }else{
                    $text= 'Inhouse';
                }
                $response[]  =[
                    'id'    =>$product->id,
                    'text'  =>'-> '.$product->product_name . '['. $text . ']'
                ];
            }else{
                $response[]  =[
                    'id'    =>$product->id,
                    'text'  =>$product->product_name
                ];
            }
        }

        return  $response;

    }
}
