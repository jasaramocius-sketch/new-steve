<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'commission')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('commission', 5, 2)->nullable()->after('cost')
                    ->comment('Per-product commission percent. NULL = use global commission.');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'commission')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('commission');
            });
        }
    }
};
