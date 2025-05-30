<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentListOnAuctionMenuList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       try{
            $menu = DB::table('backendmenus')->where('route','auctionproducts.auction-product')->first();
            if($menu)
            {
                $maxp = DB::table('backendmenus')->where('parent_id',$menu->id)->max('position');
                DB::table('backendmenus')->insert([
                    "name" => 'auctionproduct.entry_amount_list',
                    "route" => "auctionproducts.entryAmountList",
                    'is_seller' => 1,
                    'is_admin' => 1,
                    "parent_id" => $menu->id,
                    "position" => $maxp + 1,
                    "module" => "AuctionProducts"
                ]);

                $auc_per = DB::table('permissions')->where('route','auctionproducts.auction-product')->first();
                $max = DB::table('permissions')->max('id');
                $per =  [
                        'id' => $max + 1,
                        'module_id' => 53,
                        'parent_id' => $auc_per->id,
                        'module'=>'AuctionProducts',
                        'name' => 'Payments',
                        'route' => 'auctionproducts.entryAmountList',
                        'type' => 2
                        ];
                DB::table('permissions')->insert($per);
            }
       }catch(Exception $e){

       }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('', function (Blueprint $table) {

        });
    }
}
