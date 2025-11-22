<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            if (!Schema::hasColumn('attributes', 'external_attribute_set_id')) {
                $table->unsignedBigInteger('external_attribute_set_id')->nullable()->index()->after('id');
            }
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            if (!Schema::hasColumn('attribute_values', 'external_attribute_id')) {
                $table->unsignedBigInteger('external_attribute_id')->nullable()->index()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            if (Schema::hasColumn('attributes', 'external_attribute_set_id')) {
                $table->dropColumn('external_attribute_set_id');
            }
        });

        Schema::table('attribute_values', function (Blueprint $table) {
            if (Schema::hasColumn('attribute_values', 'external_attribute_id')) {
                $table->dropColumn('external_attribute_id');
            }
        });
    }
};

