<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->renameColumn('is_active', 'status');
        });
        Schema::table('pages', function (Blueprint $table) {
            $table->renameColumn('is_active', 'status');
        });
        Schema::table('coupons', function (Blueprint $table) {
            $table->renameColumn('is_active', 'status');
        });
        Schema::table('home_page_sections', function (Blueprint $table) {
            $table->renameColumn('is_active', 'status');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('is_active', 'status');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('is_active', 'status');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('status', 'is_active');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('status', 'is_active');
        });
        Schema::table('home_page_sections', function (Blueprint $table) {
            $table->renameColumn('status', 'is_active');
        });
        Schema::table('coupons', function (Blueprint $table) {
            $table->renameColumn('status', 'is_active');
        });
        Schema::table('pages', function (Blueprint $table) {
            $table->renameColumn('status', 'is_active');
        });
        Schema::table('brands', function (Blueprint $table) {
            $table->renameColumn('status', 'is_active');
        });
    }
};
