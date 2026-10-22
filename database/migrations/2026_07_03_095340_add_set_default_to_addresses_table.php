<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('addresses', 'set_default')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->boolean('set_default')->default(false)->after('zip_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('addresses', 'set_default')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->dropColumn('set_default');
            });
        }
    }
};
