<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_carriers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('delivery_estimate', 100)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('country', 150)->default('*');
            $table->string('state', 150)->nullable();
            $table->string('city', 150)->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['country', 'state', 'city', 'is_active']);
        });

        Schema::create('shipping_zone_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_carrier_id')->constrained()->restrictOnDelete();
            $table->decimal('fee', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['shipping_zone_id', 'shipping_carrier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_zone_rates');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('shipping_carriers');
    }
};
