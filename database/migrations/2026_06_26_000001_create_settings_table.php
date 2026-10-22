<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert([
            ['key' => 'header_logo', 'value' => 'BwSkuSZ7ZYGWPc4Zk3CfeFzcn49dHpx3143n4WKS.png'],
            ['key' => 'header_phone', 'value' => '+1 (234) 567-8901'],
            ['key' => 'header_support_text', 'value' => 'Contact & Support: 00 000 000 000'],
            ['key' => 'header_email', 'value' => 'admin@geniusocean.com'],
            ['key' => 'header_address', 'value' => '3584 Hickory Heights Drive , USA'],
            ['key' => 'footer_copyright', 'value' => 'COPYRIGHT &copy; :year. All Rights Reserved By STautoparts'],
            ['key' => 'header_favicon', 'value' => '1730880696Fabpng.png'],
            ['key' => 'mobile_logo', 'value' => '1730281141Whitepng.png'],
            ['key' => 'footer_logo', 'value' => '1730281141Whitepng.png'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
