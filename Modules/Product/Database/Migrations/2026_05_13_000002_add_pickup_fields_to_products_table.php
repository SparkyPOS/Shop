<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'shipping_pickup')) {
                $table->string('shipping_pickup', 10)->nullable()->after('shipping_type');
            }

            if (!Schema::hasColumn('products', 'shipping_location')) {
                $table->string('shipping_location', 255)->nullable()->after('shipping_pickup');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'shipping_location')) {
                $table->dropColumn('shipping_location');
            }

            if (Schema::hasColumn('products', 'shipping_pickup')) {
                $table->dropColumn('shipping_pickup');
            }
        });
    }
};
