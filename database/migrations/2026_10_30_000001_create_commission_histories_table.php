<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_histories', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 20);
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('old_commission', 5, 2)->nullable();
            $table->decimal('new_commission', 5, 2)->nullable();
            $table->string('changed_by_type')->nullable();
            $table->unsignedBigInteger('changed_by_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['scope', 'product_id']);
            $table->index(['changed_by_type', 'changed_by_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_histories');
    }
};