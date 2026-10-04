<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The dashboard and every report filter orders by order_date and status.
 * Without these indexes each of those queries scans the whole orders table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['order_date', 'status'], 'orders_order_date_status_index');
            $table->index('status', 'orders_status_index');
            $table->index('created_at', 'orders_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_order_date_status_index');
            $table->dropIndex('orders_status_index');
            $table->dropIndex('orders_created_at_index');
        });
    }
};
