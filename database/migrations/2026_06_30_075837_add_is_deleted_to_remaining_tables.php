<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    private array $tables = [
        'order_items', 'wishlists', 'notifications', 'admins',
        'compares', 'addresses', 'vehicles', 'followed_sellers',
        'images', 'staff', 'coupons', 'pages',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasColumn($table, 'is_deleted')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->boolean('is_deleted')->default(false);
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'is_deleted')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('is_deleted');
                });
            }
        }
    }
};
