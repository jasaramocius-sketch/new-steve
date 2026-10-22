<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('stock_deducted')->default(false)->after('coupon_discount');
            $table->string('tracking_number')->nullable()->after('stock_deducted');
            $table->string('tracking_carrier')->nullable()->after('tracking_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['stock_deducted', 'tracking_number', 'tracking_carrier']);
        });
    }
};