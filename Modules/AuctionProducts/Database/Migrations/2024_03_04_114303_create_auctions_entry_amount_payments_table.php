<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuctionsEntryAmountPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auctions_entry_amount_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auction_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->double('amount')->nullable();
            $table->unsignedBigInteger('payment_method')->nullable();
            $table->integer('status')->default(0)->comment('0= pending, 1 = paid, 2 = canceled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auctions_entry_amount_payments');
    }
}
