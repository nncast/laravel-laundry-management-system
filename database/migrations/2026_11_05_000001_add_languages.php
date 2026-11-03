<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Languages: each staff member's own choice, and the shop's default, the
 * languages staff can pick and the fallback for missing translations.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->string('locale', 10)->nullable()->after('role');
        });

        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('default_locale', 10)->default('en');
            $table->string('fallback_locale', 10)->default('en');
            $table->text('enabled_locales')->nullable();   // JSON list of codes; empty = all of config/locales.php
        });

        Cache::forget('system.settings');   // the cached copy predates these columns
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['default_locale', 'fallback_locale', 'enabled_locales']);
        });

        Schema::table('staffs', function (Blueprint $table) {
            $table->dropColumn('locale');
        });

        Cache::forget('system.settings');
    }
};
