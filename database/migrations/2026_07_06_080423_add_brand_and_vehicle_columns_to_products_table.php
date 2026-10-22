<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete()->after('category_id');
            $table->year('year')->nullable()->after('brand_id');
            $table->string('make', 100)->nullable()->after('year');
            $table->string('model', 100)->nullable()->after('make');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn(['brand_id', 'year', 'make', 'model']);
        });
    }
};
