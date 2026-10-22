<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('image_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('image_id');
            $table->unsignedBigInteger('product_id');
            $table->timestamp('created_at')->nullable();
            $table->unique(['image_id', 'product_id']);

            $table->foreign('image_id')->references('id')->on('images')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        // Backfill existing product gallery relationships into the pivot table
        DB::table('image_product')->insertUsing(
            ['image_id', 'product_id', 'created_at'],
            DB::table('images')
                ->select('id as image_id', 'attachable_id as product_id', DB::raw('NOW() as created_at'))
                ->where('attachable_type', 'App\\Models\\Product')
                ->whereNull('deleted_at')
        );
    }

    public function down()
    {
        Schema::dropIfExists('image_product');
    }
};
