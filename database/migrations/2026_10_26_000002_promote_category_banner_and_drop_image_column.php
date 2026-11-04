<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('categories', 'cover_image')) {
            DB::table('categories')
                ->where(fn ($query) => $query->whereNull('banner')->orWhere('banner', ''))
                ->whereNotNull('cover_image')
                ->where('cover_image', '!=', '')
                ->update(['banner' => DB::raw('cover_image')]);
        }

        if (Schema::hasColumn('categories', 'image')) {
            DB::table('categories')
                ->where(fn ($query) => $query->whereNull('banner')->orWhere('banner', ''))
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->update(['banner' => DB::raw('image')]);

            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('categories', 'image')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('image')->nullable();
            });

            DB::table('categories')->whereNotNull('banner')->update([
                'image' => DB::raw('banner'),
            ]);
        }
    }
};