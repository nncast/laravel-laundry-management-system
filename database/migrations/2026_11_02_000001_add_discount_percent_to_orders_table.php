<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A discount can be a percentage. When discount_percent is set, `discount`
 * holds the pesos it works out to (recomputed with the totals, so editing the
 * order keeps the same percentage); when it is null, `discount` is a fixed
 * peso amount, as before. Existing orders keep their fixed discounts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->nullable()->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('discount_percent');
        });
    }
};
