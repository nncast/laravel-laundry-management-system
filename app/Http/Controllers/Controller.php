<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

abstract class Controller
{
    /**
     * Read a Y-m-d date from the request, falling back to $default when missing/invalid.
     */
    protected function dateInput(Request $request, string $key, string $default): string
    {
        $value = (string) $request->input($key, '');

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && checkdate(
            (int) substr($value, 5, 2), (int) substr($value, 8, 2), (int) substr($value, 0, 4)
        )) {
            return $value;
        }

        return $default;
    }

    /**
     * Stream rows as a CSV download (UTF-8 with BOM so Excel shows ₱ correctly).
     *
     * @param iterable<array> $rows
     */
    protected function csvDownload(string $filename, iterable $rows)
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($out, array_map([$this, 'csvCell'], $row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Orders for a CSV export as plain rows, newest first, 1,000 at a time: the order,
     * its customer's name, its items (with service names) and its add-ons total, read
     * with three plain queries per chunk instead of building models, so a year of
     * orders exports in seconds without filling memory.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query orders to export (filters only)
     */
    protected function orderExportRows($query): \Generator
    {
        $lastId = PHP_INT_MAX;
        do {
            $chunk = (clone $query)->toBase()
                ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->where('orders.id', '<', $lastId)
                ->orderByDesc('orders.id')
                ->limit(1000)
                ->get(['orders.id', 'orders.order_number', 'orders.order_date', 'orders.currency', 'orders.subtotal',
                    'orders.discount', 'orders.total', 'orders.paid_amount', 'orders.status', 'customers.name as customer_name']);
            if ($chunk->isEmpty()) {
                break;
            }

            $ids = $chunk->pluck('id');
            $items = DB::table('order_items')
                ->join('services', 'services.id', '=', 'order_items.service_id')
                ->whereIn('order_items.order_id', $ids)
                ->get(['order_items.order_id', 'services.name', 'order_items.qty'])
                ->groupBy('order_id');
            $addons = DB::table('order_addons')->whereIn('order_id', $ids)
                ->selectRaw('order_id, SUM(price) as total')->groupBy('order_id')->pluck('total', 'order_id');

            foreach ($chunk as $row) {
                $row->items = $items[$row->id] ?? collect();
                $row->addon_total = (float) ($addons[$row->id] ?? 0);
                yield $row;
            }
            $lastId = $chunk->last()->id;
        } while ($chunk->count() === 1000);
    }

    /**
     * Neutralise spreadsheet formula injection (=, +, -, @ at the start of text).
     */
    protected function csvCell($value)
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            && !is_numeric($value)) {
            return "'" . $value;
        }

        return $value;
    }
}
