<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPusherColumnsOnGeneralSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('general_settings','pusher_key')){
            Schema::table('general_settings', function (Blueprint $table) {
                $table->string('pusher_key')->nullable();
            });
        }

        if(!Schema::hasColumn('general_settings','pusher_secret')){
            Schema::table('general_settings', function (Blueprint $table) {
                $table->string('pusher_secret')->nullable();
            });
        }

        if(!Schema::hasColumn('general_settings','pusher_cluster')){
            Schema::table('general_settings', function (Blueprint $table) {
                $table->string('pusher_cluster')->nullable();
            });
        }

        if(!Schema::hasColumn('general_settings','pusher_app_id')){
            Schema::table('general_settings', function (Blueprint $table) {
                $table->string('pusher_app_id')->nullable();
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
        Schema::table('', function (Blueprint $table) {

        });
    }
}
