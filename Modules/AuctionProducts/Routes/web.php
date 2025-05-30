<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use Illuminate\Support\Facades\Route;
use Modules\AuctionProducts\Http\Controllers\AuctionProductsController;

Route::get('/auction-products',[AuctionProductsController::class,'getAllProduct'])->name('frontend.auctionproducts.gallary');
Route::get('/auction-product/view/{id?}/{seller_product_id?}', 'AuctionProductsController@viewProduct')->name('auctionproducts.view');
Route::get('/pay-entry-amount/{id}','AuctionProductsController@payEntryAmount')->name('auction.payentryAmount');
Route::post('/auction-entry-amount-pay/{id}','AuctionProductsController@auctionEntryAmountPay')->name('auction.auctionEntryAmountPay');
Route::get('/auction/history/{id}','AuctionProductsController@auctionHistory')->name('auction.auctionHistory');

Route::middleware(['auth'])->prefix('auctionproducts')->group(function() {
    Route::get('/auction-product', 'AuctionProductsController@index')->name('auctionproducts.auction-product');//->middleware(['permission','prohibited_demo_mode']);
    Route::get('/auction-product-get-data', 'AuctionProductsController@getData')->name('auctionproducts.auction-get-product');//->middleware(['auth','seller']);
    Route::get('/auction-create', 'AuctionProductsController@create')->name('auctionproducts.auction.create');//->middleware(['auth','permission']);
    Route::post('/store', 'AuctionProductsController@store')->name('auctionproducts.store');
    Route::get('/edit/{id?}', 'AuctionProductsController@edit')->name('auctionproducts.edit');
    Route::post('/update', 'AuctionProductsController@update')->name('auctionproducts.update');
    Route::post('/delete', 'AuctionProductsController@destroy')->name('auctionproducts.destroy');
    Route::get('/view-all-bids/{id?}', 'AuctionProductsController@viewAllBids')->name('auctionproducts.view.all.bids');
    Route::get('/get-view-all-bids-data/{id?}', 'AuctionProductsController@getViewAllBidsData')->name('auctionproducts.get.view.all.bids.data');
    Route::get('/delete-bid/{id?}', 'AuctionProductsController@destroyThisBid')->name('auctionproducts.destroy.bid');
    Route::post('/place-bid', 'AuctionProductsController@placeBid')->name('auctionproducts.place.bid');
    Route::get('/settings/{id?}', 'AuctionProductsController@settings')->name('auctionproducts.settings');
    Route::get('/auction-configuration', 'AuctionProductsController@auctionConfiguration')->name('auctionproducts.configuration');
    Route::post('/auction-configuration/update', 'AuctionProductsController@auctionConfigurationuUpdate')->name('auctionproducts.configuration.update');
    Route::post('/update-auction-settings', 'AuctionProductsController@updateSettings')->name('auctionproducts.update.auction.settings');
    Route::post('/email-bidder-for-award', 'AuctionProductsController@emailBidderForAward')->name('auctionproducts.email.bidder.award');
    Route::get('/get-user-confirmation-for-order/{auction_id?}/{bid_id?}/{user_id?}', 'AuctionProductsController@getUserOrderConfirmationPage')->name('auctionproducts.get.awarded.user.confirmation');
    Route::get('/cancel-auction-order/{auction_id?}/{bid_id?}', 'AuctionProductsController@cancelAuctionOrder')->name('auctionproducts.cancel.order');
    Route::get('/confirm-auction-order/{auction_id?}/{bid_id?}', 'AuctionProductsController@confirmAuctionOrder')->name('auctionproducts.confirm.order');
    Route::get('/seller-products/get-by-ajax', 'AuctionProductsController@getSellerProductByAjax');
    Route::get('/auction-end-check/cron-job', 'AuctionProductsController@cronjob')->name('auctionproducts.auction-end.cronjob');
    Route::get('/auction-product-inhouse', 'InHousAuctionController@inhouseAuction')->name('auctionproducts.auction-product-inhouse')->middleware(['auth','seller']);
    Route::get('/auction-product-inhouse-getData', 'InHousAuctionController@getData')->name('auctionproducts.auction-product-inhouse-getData')->middleware(['auth','seller']);
    Route::get('/entry-amount-payments','AuctionProductsController@entryAmounts')->name('auctionproducts.entryAmountList')->middleware(['auth','seller']);
    Route::get('/entry-amount-data','AuctionProductsController@entryAmountData')->name('auctionproducts.entryAmountData');
    Route::get('/entry-amount/details/{id}','AuctionProductsController@entryAmountDetails')->name('auctionproducts.entryAmountDetails');
    Route::get('/entry-amount/status/change/{id}','AuctionProductsController@entryAmountChangeStatus')->name('auctionproducts.entryAmountStatusChange');
});
