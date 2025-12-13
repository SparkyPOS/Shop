<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentSellerIdToSellerAccountsTable extends Migration
{
    public function up()
    {
        Schema::table('seller_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('seller_accounts', 'parent_seller_id')) {
                $table->unsignedBigInteger('parent_seller_id')->nullable()->after('user_id');
                // Optional FK to users table; keep nullable for backward compatibility
                try {
                    $table->foreign('parent_seller_id')->references('id')->on('users')->onDelete('set null');
                } catch (\Throwable $e) {
                    // Some DBs may already have constraints management; ignore if cannot add
                }
            }
        });
    }

    public function down()
    {
        Schema::table('seller_accounts', function (Blueprint $table) {
            try {
                $table->dropForeign(['parent_seller_id']);
            } catch (\Throwable $e) {
                // ignore
            }
            if (Schema::hasColumn('seller_accounts', 'parent_seller_id')) {
                $table->dropColumn('parent_seller_id');
            }
        });
    }
}

