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
        if (! Schema::hasColumn('products', 'reviews_data')) {
            Schema::table('products', function (Blueprint $table) {
                $table->longText('reviews_data')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('products', 'reviews_data')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('reviews_data');
            });
        }
    }
};
