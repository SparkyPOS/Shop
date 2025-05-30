<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPricesColumnsOnAuctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('auctions','increment_price'))
        {
            Schema::table('auctions', function (Blueprint $table) {
                $table->double('increment_price')->default(0);
            });
        }

        if(!Schema::hasColumn('auctions','reserve_price'))
        {
            Schema::table('auctions', function (Blueprint $table) {
                $table->double('reserve_price')->default(0);
            });
        }

        if(!Schema::hasColumn('auctions','entry_amount'))
        {
            Schema::table('auctions', function (Blueprint $table) {
                $table->double('entry_amount')->default(0);
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasColumn('auctions','increment_price'))
        {
            Schema::table('auctions', function (Blueprint $table) {
                $table->dropColumn('increment_price');
            });
        }

        if(Schema::hasColumn('auctions','reserve_price'))
        {
            Schema::table('auctions', function (Blueprint $table) {
                $table->dropColumn('reserve_price');
            });
        }

        if(Schema::hasColumn('auctions','entry_amount'))
        {
            Schema::table('auctions', function (Blueprint $table) {
                $table->dropColumn('entry_amount');
            });
        }
    }
}
