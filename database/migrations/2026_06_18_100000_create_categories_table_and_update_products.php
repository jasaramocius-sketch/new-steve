<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
            $table->index(['parent_id', 'slug']);
        });

        // Add category_id FK to products
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });

        // Drop old string columns
        $dropCols = [];
        if (Schema::hasColumn('products', 'category')) {
            $dropCols[] = 'category';
        }
        if (Schema::hasColumn('products', 'subcategory')) {
            $dropCols[] = 'subcategory';
        }
        if (Schema::hasColumn('products', 'childcategory')) {
            $dropCols[] = 'childcategory';
        }
        if (! empty($dropCols)) {
            Schema::table('products', function (Blueprint $table) use ($dropCols) {
                $table->dropColumn($dropCols);
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->string('childcategory')->nullable();
        });
        Schema::dropIfExists('categories');
    }
};
