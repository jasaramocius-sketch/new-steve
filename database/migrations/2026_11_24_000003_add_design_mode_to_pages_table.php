<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('design_mode', 16)->default('classic')->after('content_blocks');
        });

        // Backfill from existing data without changing any live design:
        // pages that already use both editors keep rendering both.
        DB::table('pages')->orderBy('id')->each(function ($page) {
            $blocks = json_decode((string) $page->content_blocks, true);
            $hasBlocks = is_array($blocks) && $blocks !== [];
            $hasContent = trim(strip_tags((string) $page->content)) !== '';

            $mode = $hasBlocks ? ($hasContent ? 'both' : 'builder') : 'classic';

            DB::table('pages')->where('id', $page->id)->update(['design_mode' => $mode]);
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('design_mode');
        });
    }
};
