<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Receipts, claim tickets and job orders: when an order will be ready, its
 * weight, who took each payment, and what the shop prints on its documents.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->date('ready_by')->nullable()->after('order_date');
            $table->decimal('weight', 8, 2)->nullable()->after('ready_by');   // kg
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('received_by')->nullable()->after('payment_method');   // staffs.id
        });

        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('receipt_logo')->nullable();
            $table->string('receipt_header', 500)->nullable();
            $table->string('receipt_thank_you')->nullable();
            $table->string('receipt_footer', 500)->nullable();
            $table->text('receipt_pickup_instructions')->nullable();
            $table->text('receipt_terms')->nullable();
            $table->string('receipt_paper', 10)->default('80mm');
            $table->boolean('receipt_show_qr')->default(true);
            $table->boolean('receipt_show_barcode')->default(true);
            $table->unsignedTinyInteger('turnaround_days')->default(2);
        });

        Cache::forget('system.settings');   // the cached copy predates these columns
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'receipt_logo', 'receipt_header', 'receipt_thank_you', 'receipt_footer', 'receipt_pickup_instructions',
                'receipt_terms', 'receipt_paper', 'receipt_show_qr', 'receipt_show_barcode', 'turnaround_days',
            ]);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('received_by');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['ready_by', 'weight']);
        });

        Cache::forget('system.settings');
    }
};
