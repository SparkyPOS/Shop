<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuctionsEntryAmountGatewayInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auctions_entry_amount_gateway_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gateway_id')->nullable();
            $table->unsignedBigInteger('entry_amount_payment_id')->nullable();
            $table->text('payment_info')->nullable();
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
        Schema::dropIfExists('auctions_entry_amount_gateway_infos');
    }
}
