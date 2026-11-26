<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'tab_label_1')) {
                $table->string('tab_label_1')->nullable()->after('status');
            }
            if (! Schema::hasColumn('products', 'tab_label_2')) {
                $table->string('tab_label_2')->nullable()->after('tab_label_1');
            }
            if (! Schema::hasColumn('products', 'tab_label_3')) {
                $table->string('tab_label_3')->nullable()->after('tab_label_2');
            }
            if (! Schema::hasColumn('products', 'policy_text')) {
                $table->text('policy_text')->nullable()->after('tab_label_3');
            }
        });
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['tab_label_1', 'tab_label_2', 'tab_label_3', 'policy_text'],
            fn ($column) => Schema::hasColumn('products', $column)
        ));

        if ($columns !== []) {
            Schema::table('products', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
