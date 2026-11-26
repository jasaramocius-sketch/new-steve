<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_fitments')) {
            return;
        }

        Schema::create('product_fitments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('year_from')->nullable();
            $table->smallInteger('year_to')->nullable();
            $table->string('make', 100)->nullable();
            $table->string('model', 150)->nullable();
            $table->string('engine', 150)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->index(['product_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_fitments');
    }
};
