<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'approval_status')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('approval_status', 20)->default('approved')->after('status');
                $table->index('approval_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'approval_status')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['approval_status']);
                $table->dropColumn('approval_status');
            });
        }
    }
};
