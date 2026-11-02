<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The business currency and number/date/time formats become settings, and every
 * order remembers the currency it was taken in, so changing the currency later
 * never relabels old amounts. Orders made before this were in Philippine pesos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('currency', 3)->default('PHP');
            $table->string('number_format', 20)->default('comma_dot');
            $table->string('date_format', 20)->default('M j, Y');
            $table->string('time_format', 3)->default('12h');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('currency', 3)->default('PHP')->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['currency', 'number_format', 'date_format', 'time_format']);
        });
    }
};
