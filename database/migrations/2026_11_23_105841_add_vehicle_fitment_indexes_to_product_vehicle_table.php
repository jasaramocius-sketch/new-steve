<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(DB::select("SHOW INDEX FROM product_vehicle"))->pluck('Key_name')->unique()->values()->all();

        Schema::table('product_vehicle', function (Blueprint $table) use ($indexes) {
            // Covers make-only, make+year, make+year+model filters (e.g. /search?make=Toyota).
            if (! in_array('idx_my', $indexes, true) && ! in_array('product_vehicle_make_year_index', $indexes, true)) {
                $table->index(['make', 'year'], 'idx_my');
            }

            // Covers model-only and model+make lookups; also helps make+model when combined with idx_my.
            if (! in_array('idx_model', $indexes, true) && ! in_array('product_vehicle_model_index', $indexes, true)) {
                $table->index('model', 'idx_model');
            }
        });
    }

    public function down(): void
    {
        $indexes = collect(DB::select("SHOW INDEX FROM product_vehicle"))->pluck('Key_name')->unique()->values()->all();

        Schema::table('product_vehicle', function (Blueprint $table) use ($indexes) {
            if (in_array('idx_my', $indexes, true)) {
                $table->dropIndex('idx_my');
            }
            if (in_array('idx_model', $indexes, true)) {
                $table->dropIndex('idx_model');
            }
        });
    }
};
