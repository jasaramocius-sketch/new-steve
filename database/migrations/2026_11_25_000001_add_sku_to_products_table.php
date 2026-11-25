<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'sku')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            // Appended (no "after") so MySQL can use an instant ADD COLUMN on the large catalogue.
            $table->string('sku', 100)->nullable()->unique();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('products', 'sku')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sku');
        });
    }
};
