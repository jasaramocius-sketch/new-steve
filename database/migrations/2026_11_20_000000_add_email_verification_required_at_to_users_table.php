<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'email_verification_required_at')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->timestamp('email_verification_required_at')->nullable()->after('email_verified_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'email_verification_required_at')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('email_verification_required_at');
            });
        }
    }
};
