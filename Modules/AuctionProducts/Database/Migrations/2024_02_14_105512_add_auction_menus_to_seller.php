<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAuctionMenusToSeller extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $routes = [
            'auctionproducts.auction-product',
            'auctionproducts.auction-product',
            'auctionproducts.auction.create',
            'auctionproducts.configuration'
        ];

        foreach($routes as $route)
        {
            $auction = DB::table('backendmenus')->where('route',$route)->first();
            if($auction)
            {
                DB::table('backendmenus')->where('route',$route)->update([
                    "is_seller" => 1,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $routes = [
            'auctionproducts.auction-product',
            'auctionproducts.auction-product',
            'auctionproducts.auction.create',
            'auctionproducts.configuration'
        ];

        foreach($routes as $route)
        {
            $auction = DB::table('backendmenus')->where('route',$route)->first();
            if($auction)
            {
                DB::table('backendmenus')->where('route',$route)->update([
                    "is_seller" => 0,
                ]);
            }
        }
    }
}
