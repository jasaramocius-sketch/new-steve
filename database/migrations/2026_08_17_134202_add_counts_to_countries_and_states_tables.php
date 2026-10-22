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
        Schema::table('countries', function (Blueprint $table) {
            $table->unsignedInteger('states_count')->default(0)->after('name');
        });

        Schema::table('states', function (Blueprint $table) {
            $table->unsignedInteger('cities_count')->default(0)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('states', function (Blueprint $table) {
            $table->dropColumn('cities_count');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('states_count');
        });
    }
};
