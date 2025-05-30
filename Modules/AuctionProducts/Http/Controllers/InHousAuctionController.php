<?php

namespace Modules\AuctionProducts\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Seller\Services\ProductService;

use Yajra\DataTables\Facades\DataTables;

class InHousAuctionController extends Controller
{
    protected $productService;
    public function __construct(ProductService $productService)
    {
        $this->middleware('maintenance_mode');
        $this->productService = $productService;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('auctionproducts::index');
    }
    public function inhouseAuction()
    {
        return view('auctionproducts::inhous.auctionproducts');
    }
    public function getData()
    {
        $status_slider = '_all_product_';
        if(isset($_GET['table'])){
            $products = $this->productService->getFilterdProduct(['table' => $_GET['table']]);
            $status_slider = '_'.$_GET['table'].'_';
        }else{
            $products = $this->productService->getAll();
        }
        return DataTables::of($products)
        ->addIndexColumn()
        ->editColumn('product_name', function ($products) {
            return $products->product_name ?? '';
        })
        ->addColumn('bid_starting_amount', function ($products) {
            return getNumberTranslate(@$products->product->auction_products ?? 0); 
        })
        ->addColumn('auction_start_date', function ($products) {
            return getNumberTranslate($products->auction_start_date ?? 0); 
        })
        ->addColumn('auction_end_date', function ($products) {
            return getNumberTranslate($products->auction_end_date ?? 0); 
        })
        ->addColumn('total_bids', function ($products) {
            return getNumberTranslate($products-> total_bids ?? 0); 
        })
        ->addColumn('stock', function($products){
            return view('auctionproducts::inhous.components._stock_td',compact('products'));
        })
        ->addColumn('status', function($products) use ($status_slider){
            return view('auctionproducts::inhous.components._status_td',compact('products','status_slider'));
        })
        ->addColumn('action',function($products){
            return view('auctionproducts::inhous.components._action_td',compact('products'));
        })
        ->rawColumns(['stock','status','action'])
        ->toJson();
    }
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('auctionproducts::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
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
        return view('auctionproducts::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
