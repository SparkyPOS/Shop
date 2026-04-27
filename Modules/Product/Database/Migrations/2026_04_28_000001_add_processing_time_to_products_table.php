<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'processing_time')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('processing_time', 100)->nullable()->after('shipping_cost');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'processing_time')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('processing_time');
            });
        }
    }
};

