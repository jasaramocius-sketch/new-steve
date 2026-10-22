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
            // baki tables...
        ];

        foreach ($tables as $tableName) {

            // deleted_at nahi hai to skip
            if (! Schema::hasColumn($tableName, 'deleted_at')) {
                continue;
            }

            // is_deleted pehle se hai to skip
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
