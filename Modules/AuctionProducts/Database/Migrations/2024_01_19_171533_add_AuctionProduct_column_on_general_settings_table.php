<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAuctionProductColumnOnGeneralSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('general_settings','AuctionProducts'))
        {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->integer('AuctionProducts')->default(0);
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
        if(Schema::hasColumn('general_settings','AuctionProducts'))
        {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->dropColumn('AuctionProducts');
            });
        }

    }
}
