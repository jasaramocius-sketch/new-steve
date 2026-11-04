<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'rating')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('rating', 3, 1)->default(0);
            });
        }

        if (! Schema::hasColumn('products', 'reviews')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('reviews')->default(0);
            });
        }
    }

    public function down(): void
    {
        // These columns may already exist in databases created from the original products migration.
    }
};