<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 12)->unique();
            $table->string('flutter_code', 12)->nullable();
            $table->boolean('rtl')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $languages = [];
        foreach (config('languages', []) as $code => $language) {
            $languages[] = [
                'name' => $language['name'] ?? strtoupper($code),
                'code' => $code,
                'flutter_code' => $code,
                'rtl' => $code === 'ar',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($languages !== []) {
            DB::table('languages')->insert($languages);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
