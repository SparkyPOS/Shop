<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('auction_title')->nullable();
            $table->unsignedBigInteger('seller_product_id');
            $table->integer('quantity')->default(1);
            $table->unsignedBigInteger('starting_bidding_price')->nullable();
            $table->date('auction_start_date')->nullable();
            $table->date('auction_end_date')->nullable();
            $table->text('auction_description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('total_bids')->default(0);
            $table->integer('bidder_award_system')->default(1);
            $table->integer('is_send')->default(0);
            $table->timestamps();
            $table->foreign('seller_product_id')->references('id')->on('seller_products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auctions');
    }
}
