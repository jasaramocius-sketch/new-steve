<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('followed_sellers', 'seller_id')) {
            Schema::table('followed_sellers', function (Blueprint $table) {
                $table->foreignId('seller_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
                $table->unique(['user_id', 'seller_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('followed_sellers', 'seller_id')) {
            Schema::table('followed_sellers', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'seller_id']);
                $table->dropForeign(['seller_id']);
                $table->dropColumn('seller_id');
            });
        }
    }
};
