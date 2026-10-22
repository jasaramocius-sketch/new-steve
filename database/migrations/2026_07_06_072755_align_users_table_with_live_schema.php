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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'referred_by')) {
                $table->integer('referred_by')->nullable()->after('id');
            }
            if (! Schema::hasColumn('users', 'provider')) {
                $table->string('provider', 255)->nullable()->after('referred_by');
            }
            if (! Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id', 50)->nullable()->after('provider');
            }
            if (! Schema::hasColumn('users', 'refresh_token')) {
                $table->text('refresh_token')->nullable()->after('provider_id');
            }
            if (! Schema::hasColumn('users', 'access_token')) {
                $table->longText('access_token')->nullable()->after('refresh_token');
            }
            if (! Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type', 20)->default('customer')->after('access_token');
            }
            if (! Schema::hasColumn('users', 'verification_code')) {
                $table->text('verification_code')->nullable()->after('email_verified_at');
            }
            if (! Schema::hasColumn('users', 'new_email_verificiation_code')) {
                $table->text('new_email_verificiation_code')->nullable()->after('verification_code');
            }
            if (! Schema::hasColumn('users', 'device_token')) {
                $table->string('device_token', 255)->nullable()->after('remember_token');
            }
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar', 256)->nullable()->after('device_token');
            }
            if (! Schema::hasColumn('users', 'avatar_original')) {
                $table->string('avatar_original', 256)->nullable()->after('avatar');
            }
            if (! Schema::hasColumn('users', 'state')) {
                $table->string('state', 30)->nullable()->after('country');
            }
            if (! Schema::hasColumn('users', 'postal_code')) {
                $table->string('postal_code', 20)->nullable()->after('city');
            }
            if (! Schema::hasColumn('users', 'balance')) {
                $table->double('balance', 20, 2)->default(0.00)->after('phone');
            }
            if (! Schema::hasColumn('users', 'banned')) {
                $table->tinyInteger('banned')->default(0)->after('balance');
            }
            if (! Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code', 255)->nullable()->after('banned');
            }
            if (! Schema::hasColumn('users', 'customer_package_id')) {
                $table->integer('customer_package_id')->nullable()->after('referral_code');
            }
            if (! Schema::hasColumn('users', 'remaining_uploads')) {
                $table->integer('remaining_uploads')->default(0)->after('customer_package_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'referred_by', 'provider', 'provider_id', 'refresh_token', 'access_token',
                'user_type', 'verification_code', 'new_email_verificiation_code',
                'device_token', 'avatar', 'avatar_original', 'state', 'postal_code',
                'balance', 'banned', 'referral_code', 'customer_package_id', 'remaining_uploads',
            ];
            $table->dropColumn(array_intersect($columns, Schema::getColumnListing('users')));
        });
    }
};
