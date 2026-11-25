<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_fitments')) {
            return;
        }

        Schema::table('product_fitments', function (Blueprint $table) {
            if (! Schema::hasColumn('product_fitments', 'notes')) {
                $table->string('notes', 500)->nullable()->after('engine');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_fitments')) {
            return;
        }

        Schema::table('product_fitments', function (Blueprint $table) {
            if (Schema::hasColumn('product_fitments', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
