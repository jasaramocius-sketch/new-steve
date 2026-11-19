<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('shipping_cost', 10, 2)->nullable()->default(0)->after('cost');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->decimal('shipping_cost', 10, 2)->nullable()->default(0)->after('location');
        });

        Schema::table('shipping_zones', function (Blueprint $table) {
            $table->decimal('cost', 10, 2)->nullable()->default(0)->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('shipping_cost');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('shipping_cost');
        });

        Schema::table('shipping_zones', function (Blueprint $table) {
            $table->dropColumn('cost');
        });
    }
};
