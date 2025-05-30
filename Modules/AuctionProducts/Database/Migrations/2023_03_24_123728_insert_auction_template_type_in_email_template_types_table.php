<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertAuctionTemplateTypeInEmailTemplateTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $max = DB::table('email_template_types')->max('id');
        if(Schema::hasTable('email_template_types')){
            DB::statement("INSERT INTO `email_template_types` (`id`, `type`, `created_at`, `updated_at`) VALUES
                ($max + 1, 'auction_bidder_template', NULL, '2023-03-24 12:40:47'),
                ($max + 2, 'auction_seller_template', NULL, '2023-03-24 12:40:47')");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('email_template_types');
    }
}
