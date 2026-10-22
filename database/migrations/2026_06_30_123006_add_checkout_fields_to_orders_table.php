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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_address_id')->nullable()->constrained('addresses')->onDelete('set null');
            $table->text('shipping_details')->nullable();
            $table->string('delivery_type')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('additional_info')->nullable();
            $table->decimal('shipping_fee', 10, 2)->default(0.00);
            $table->decimal('tax', 10, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_address_id']);
            $table->dropColumn([
                'shipping_address_id',
                'shipping_details',
                'delivery_type',
                'payment_method',
                'additional_info',
                'shipping_fee',
                'tax',
            ]);
        });
    }
};
