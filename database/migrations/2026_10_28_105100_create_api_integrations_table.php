<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_integrations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['courier', 'payment', 'sms', 'email', 'whatsapp', 'other'])->default('courier');
            $table->string('provider')->index(); // shiprocket, delhivery, pickrr, custom
            $table->string('name');
            $table->string('base_url')->nullable();
            $table->enum('auth_type', ['none', 'bearer', 'api_key', 'basic', 'oauth'])->default('bearer');
            $table->longText('credentials')->nullable(); // encrypted JSON
            $table->longText('endpoints')->nullable(); // JSON
            $table->longText('config')->nullable(); // JSON extra settings
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->integer('priority')->default(0);
            $table->timestamp('last_tested_at')->nullable();
            $table->text('last_test_result')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_integrations');
    }
};
