<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes measured against 100,000 orders and 50,000 customers:
 * - orders (status, order_date, currency, total, paid_amount, discount) holds everything
 *   the dashboard and the sales report count and sum, so those queries read the index
 *   instead of every row (status tabs, unpaid balance, revenue, overdue, the chart);
 * - orders (customer_id, status, order_date) for a customer's orders and the status
 *   tabs of a search by customer name;
 * - order_items (order_id, qty) for "items sold";
 * - payments.created_at for "collected today" and the daily report;
 * - customers.name and contact for type-ahead search (phone numbers by prefix).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'order_date', 'currency', 'total', 'paid_amount', 'discount'], 'orders_status_date_money_index');
            $table->index(['customer_id', 'status', 'order_date'], 'orders_customer_status_index');
        });
        // The composite index covers customer_id now; a plain one left by down() is redundant
        if (Schema::hasIndex('orders', 'orders_customer_id_index')) {
            Schema::table('orders', fn (Blueprint $table) => $table->dropIndex('orders_customer_id_index'));
        }
        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['order_id', 'qty'], 'order_items_order_qty_index');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->index('created_at', 'payments_created_at_index');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->index('name', 'customers_name_index');
            $table->index('contact', 'customers_contact_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_date_money_index');
        });
        // MySQL lets the customer_id foreign key use the composite index (dropping its own),
        // so give it a plain index back before removing the composite one
        if (!Schema::hasIndex('orders', 'orders_customer_id_index')) {
            Schema::table('orders', fn (Blueprint $table) => $table->index('customer_id', 'orders_customer_id_index'));
        }
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_customer_status_index');
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_order_qty_index');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_created_at_index');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('customers_name_index');
            $table->dropIndex('customers_contact_index');
        });
    }
};
