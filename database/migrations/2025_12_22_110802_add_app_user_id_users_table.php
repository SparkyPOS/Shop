<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        if(Schema::hasTable('users') && !Schema::hasColumn('users', 'app_user_id')) {
            Schema::table('users', function(Blueprint $table) {
                $table->unsignedBigInteger('app_user_id')->after('stripe_account_id')->default(NULL)->comment('store sparkypos userId');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        if(Schema::hasTable('users') && Schema::hasColumn('users', 'app_user_id')) {
            Schema::table('users', function(Blueprint $table) {
                $table->dropColumn(['app_user_id']);
            });
        }
    }
};
