<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('file_path', 500);
            $table->string('event', 20); // created, updated, deleted
            $table->string('content_hash', 32)->nullable();
            $table->string('backup_path', 500)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('event');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_revisions');
    }
};
