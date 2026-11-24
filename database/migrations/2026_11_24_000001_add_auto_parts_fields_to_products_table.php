<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = [
                'meta_title' => fn () => $table->string('meta_title', 255)->nullable(),
                'meta_description' => fn () => $table->text('meta_description')->nullable(),
                'oem_numbers_json' => fn () => $table->json('oem_numbers_json')->nullable(),
                'interchange_numbers_json' => fn () => $table->json('interchange_numbers_json')->nullable(),
                'weight_kg' => fn () => $table->decimal('weight_kg', 10, 2)->nullable(),
                'dimensions_json' => fn () => $table->json('dimensions_json')->nullable(),
                'condition' => fn () => $table->string('condition', 40)->nullable(),
                'warranty_months' => fn () => $table->unsignedInteger('warranty_months')->nullable(),
                'warranty_text' => fn () => $table->string('warranty_text', 255)->nullable(),
            ];

            foreach ($columns as $column => $add) {
                if (! Schema::hasColumn('products', $column)) {
                    $add();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = [
                'meta_title',
                'meta_description',
                'oem_numbers_json',
                'interchange_numbers_json',
                'weight_kg',
                'dimensions_json',
                'condition',
                'warranty_months',
                'warranty_text',
            ];

            $existing = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('products', $column)));

            if ($existing) {
                $table->dropColumn($existing);
            }
        });
    }
};
