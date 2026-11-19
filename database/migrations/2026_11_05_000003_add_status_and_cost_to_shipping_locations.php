<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('name');
        });

        Schema::table('states', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('name');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->boolean('status')->default(true)->after('name');
            $table->decimal('cost', 12, 2)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(['status', 'cost']);
        });

        Schema::table('states', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
