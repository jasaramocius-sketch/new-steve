<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tables = [
        'users',
        'categories',
        'products',
        'orders',
        'blogs',
        'wishlists',
        'compares',
        'followed_sellers',
        'notifications',
        'addresses',
        'vehicles',
        'order_items',
        'admins',
        'staff',
        'brands',
        'coupons',
        'pages',
        'blog_categories',
        'home_page_sections',
        'contacts',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->softDeletes();
                    $table->boolean('is_deleted')->default(false)->after('deleted_at');
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                    $table->dropColumn('is_deleted');
                });
            }
        }
    }
};
