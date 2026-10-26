<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'banner')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('banner')->nullable();
            });
        }

        if (! Schema::hasColumn('categories', 'icon')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('icon')->nullable();
            });
        }

        if (! Schema::hasColumn('categories', 'cover_image')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('cover_image')->nullable();
            });
        }

        if (Schema::hasColumn('categories', 'image')) {
            DB::table('categories')
                ->whereNull('cover_image')
                ->whereNotNull('image')
                ->update(['cover_image' => DB::raw('image')]);
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['banner', 'icon', 'cover_image'],
            fn ($column) => Schema::hasColumn('categories', $column)
        ));

        if ($columns !== []) {
            Schema::table('categories', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};