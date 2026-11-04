<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'users',
            'products',
            'categories',
            'blogs',
            'blog_categories',
            'orders',
            'contacts',
            // Remaining tables...
        ];

        foreach ($tables as $tableName) {

            // Skip tables without deleted_at.
            if (! Schema::hasColumn($tableName, 'deleted_at')) {
                continue;
            }

            // Skip tables that already have is_deleted.
            if (Schema::hasColumn($tableName, 'is_deleted')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->tinyInteger('is_deleted')
                    ->default(0)
                    ->after('deleted_at');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'users',
            'products',
            'categories',
            'blogs',
            'blog_categories',
            'orders',
            'contacts',
        ];

        foreach ($tables as $tableName) {

            if (! Schema::hasColumn($tableName, 'is_deleted')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('is_deleted');
            });
        }
    }
};
