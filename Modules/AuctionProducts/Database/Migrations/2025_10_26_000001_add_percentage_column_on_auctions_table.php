<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPercentageColumnOnAuctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('auctions', 'percentage')) {
            Schema::table('auctions', function (Blueprint $table) {
                $table->double('percentage')->default(0)->after('entry_amount');
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
        if (Schema::hasColumn('auctions', 'percentage')) {
            Schema::table('auctions', function (Blueprint $table) {
                $table->dropColumn('percentage');
            });
        }
    }
}

