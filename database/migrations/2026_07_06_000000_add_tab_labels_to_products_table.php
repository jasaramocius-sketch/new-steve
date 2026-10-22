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
        Schema::table('products', function (Blueprint $table) {
            $table->string('tab_label_1')->nullable()->after('status');
            $table->string('tab_label_2')->nullable()->after('tab_label_1');
            $table->string('tab_label_3')->nullable()->after('tab_label_2');
            $table->text('policy_text')->nullable()->after('tab_label_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['tab_label_1', 'tab_label_2', 'tab_label_3', 'policy_text']);
        });
    }
};
