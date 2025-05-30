<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\RolePermission\Entities\Permission;
use Modules\SidebarManager\Entities\Backendmenu;

class CreateAuctionProductsPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        {
            $permission = [
                ['id' => 743, 'module_id' => 53, 'parent_id' => null, 'module'=>'AuctionProducts', 'name' => 'Auction', 'route' => 'auctionproducts.auction-product', 'type' => 1],
                ['id' => 744, 'module_id' => 53, 'parent_id' => 743, 'module'=>'AuctionProducts', 'name' => 'Auction List', 'route' => 'auctionproducts.auction-product', 'type' => 2],
                ['id' => 745, 'module_id' => 53, 'parent_id' => 743, 'module'=>'AuctionProducts', 'name' => 'Add New Auction', 'route' => 'auctionproducts.auction.create', 'type' => 2],
                ['id' => 747, 'module_id' => 53, 'parent_id' => 743, 'module'=>'AuctionProducts', 'name' => 'Auction Configuration', 'route' => 'auctionproducts.configuration', 'type' => 2]
            ];
            try{
                DB::table('permissions')->insert($permission);

                if(Schema::hasTable('backendmenus')){
                    $sql = [
                        ['parent_id' => 53, 'is_admin' => 1,'is_seller' => 1, 'icon' =>'ti-announcement', 'module'=>'AuctionProducts','name' => 'auctionproduct.auction', 'route' => 'auctionproducts.auction-product', 'position' => 2, 'children'=>[
                            ['is_admin' => 1,'is_seller' => 1, 'icon' =>null, 'module'=>'AuctionProducts','name' => 'auctionproduct.auction_list', 'route' => 'auctionproducts.auction-product', 'position' => 3],//Submenu
                            ['is_admin' => 1,'is_seller' => 1, 'icon' =>null, 'module'=>'AuctionProducts','name' => 'auctionproduct.add_new_auction', 'route' => 'auctionproducts.auction.create', 'position' => 3],//Submenu
                            ['is_admin' => 1,'is_seller' => 1, 'icon' =>null, 'module'=>'AuctionProducts','name' => 'auctionproduct.auction_configuration', 'route' => 'auctionproducts.configuration', 'position' => 3],//Submenu
                        ]],
                    ];
                    foreach($sql as $menu){
                        $children = null;
                        if(array_key_exists('children',$menu)){
                            $children = $menu['children'];
                            unset( $menu['children']);
                        }
                        $parent = Backendmenu::create($menu);
                        if($children){
                            foreach($children as $menu){
                                $sub_children = null;
                                if(array_key_exists('children',$menu)){
                                    $sub_children = $menu['children'];
                                    unset( $menu['children']);
                                }
                                $menu['parent_id'] = $parent->id;
                                $parent_children = Backendmenu::create($menu);
                                if($sub_children){
                                    foreach($sub_children as $menu){
                                        $subsubmenu['parent_id'] = $parent_children->id;
                                        Backendmenu::create($subsubmenu);
                                    }
                                }
                            }
                        }
                    }
                }
            }catch(Exception $e){
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
        Permission::destroy([743,744,745,747]);
        Backendmenu::destroy([235,236,237,239]);
    }
}
