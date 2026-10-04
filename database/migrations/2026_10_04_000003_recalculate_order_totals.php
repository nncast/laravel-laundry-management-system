<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Earlier versions saved stale totals when an order was edited in the POS
 * (totals were computed from the old items before the new items were written)
 * and stored subtotal inconsistently (sometimes items only, sometimes items +
 * add-ons). Editing an order could also reset paid_amount to 0 while its
 * payment records were kept. Recompute every order from its stored rows:
 *
 *   subtotal    = sum(order_items.total)
 *   total       = max(0, subtotal + sum(order_addons.price) - discount)
 *   paid_amount = sum(payments.amount)   (only for orders that have payments)
 */
return new class extends Migration
{
    public function up(): void
    {
        $itemTotals = DB::table('order_items')
            ->select('order_id', DB::raw('SUM(total) as items_total'))
            ->groupBy('order_id')
            ->pluck('items_total', 'order_id');

        $addonTotals = DB::table('order_addons')
            ->select('order_id', DB::raw('SUM(price) as addons_total'))
            ->groupBy('order_id')
            ->pluck('addons_total', 'order_id');

        $paymentTotals = DB::table('payments')
            ->select('order_id', DB::raw('SUM(amount) as paid_total'))
            ->groupBy('order_id')
            ->pluck('paid_total', 'order_id');

        DB::table('orders')
            ->select('id', 'discount', 'subtotal', 'total', 'paid_amount', 'order_date')
            ->orderBy('id')
            ->chunk(500, function ($orders) use ($itemTotals, $addonTotals, $paymentTotals) {
                foreach ($orders as $order) {
                    $items = round((float) ($itemTotals[$order->id] ?? 0), 2);
                    $addons = round((float) ($addonTotals[$order->id] ?? 0), 2);
                    $total = max(0, round($items + $addons - (float) $order->discount, 2));

                    $changes = [];
                    if (abs((float) $order->subtotal - $items) > 0.001) {
                        $changes['subtotal'] = $items;
                    }
                    if (abs((float) $order->total - $total) > 0.001) {
                        $changes['total'] = $total;
                    }
                    if (isset($paymentTotals[$order->id])) {
                        $paid = round((float) $paymentTotals[$order->id], 2);
                        if (abs((float) $order->paid_amount - $paid) > 0.001) {
                            $changes['paid_amount'] = $paid;
                        }
                    }
                    // Normalise "Y-m-d 00:00:00" to "Y-m-d" (SQLite stores text)
                    if (is_string($order->order_date) && strlen($order->order_date) > 10) {
                        $changes['order_date'] = substr($order->order_date, 0, 10);
                    }

                    if ($changes) {
                        DB::table('orders')->where('id', $order->id)->update($changes);
                    }
                }
            });
    }

    public function down(): void
    {
        // Data correction only – nothing to roll back.
    }
};
