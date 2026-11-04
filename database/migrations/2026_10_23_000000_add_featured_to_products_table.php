<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'featured')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('featured')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'featured')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('featured');
            });
        }
    }
};