<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOutBidNoteEmailTemplateOnAuctions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $time = \Carbon\Carbon::now();
        $max = DB::table('email_template_types')->max('id');
        if(Schema::hasTable('email_template_types')){
            $types = [
                [
                    "id" => $max + 1,
                    "type" => 'auction_out_bid_note_template',
                    "created_at" => $time,
                    "updated_at" => null,
                ],

                [
                    "id" => $max + 2,
                    "type" => 'auction_won_template',
                    "created_at" => $time,
                    "updated_at" => null,
                ],

                [
                    "id" => $max + 3,
                    "type" => 'auction_fail_template',
                    "created_at" => $time,
                    "updated_at" => null,
                ],

                [
                    "id" => $max + 4,
                    "type" => 'auction_buy_now_template',
                    "created_at" => $time,
                    "updated_at" => null,
                ],

                [
                    "id" => $max + 5,
                    "type" => 'auction_finished_template',
                    "created_at" => $time,
                    "updated_at" => null,
                ],

                [
                    "id" => $max + 6,
                    "type" => 'auction_relist_template',
                    "created_at" => $time,
                    "updated_at" => null,
                ],

                [
                    "id" => $max + 7,
                    "type" => 'auction_end_soon_template',
                    "created_at" => $time,
                    "updated_at" => null,
                ],

            ];

            DB::table('email_template_types')->insert($types);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
