<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Application tables that still need Laravel soft-delete support.
     *
     * System tables (cache, jobs, sessions, migrations, password resets),
     * pivot tables (blog_tag, image_product, product_vehicle, etc.) and
     * legacy/backup tables are intentionally excluded.
     */
    private array $tables = [
        'admins',
        'api_integrations',
        'brands',
        'commission_histories',
        'contacts',
        'currencies',
        'home_page_sections',
        'languages',
        'pickup_points',
        'return_requests',
        'settings',
        'staff',
        'subscribers',
        'tags',
        'tax_types',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'deleted_at')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Faq uses TracksIsDeleted, but the column was missing from the table.
        if (Schema::hasTable('faqs') && ! Schema::hasColumn('faqs', 'is_deleted')) {
            Schema::table('faqs', function (Blueprint $table) {
                $table->boolean('is_deleted')->default(false)->after('deleted_at');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'deleted_at')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('faqs') && Schema::hasColumn('faqs', 'is_deleted')) {
            Schema::table('faqs', function (Blueprint $table) {
                $table->dropColumn('is_deleted');
            });
        }
    }
};
