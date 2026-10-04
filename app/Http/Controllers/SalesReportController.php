<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        [$startDate, $endDate] = $this->range($request);

        return view('report-sales', [
            'salesData' => $this->getSalesData($startDate, $endDate),
            'summary' => $this->getSummaryStats($startDate, $endDate),
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function download(Request $request)
    {
        [$startDate, $endDate] = $this->range($request);

        $salesData = $this->getSalesData($startDate, $endDate);
        $summary = $this->getSummaryStats($startDate, $endDate);
        $money = fn ($v) => number_format((float) $v, 2, '.', '');

        $rows = (function () use ($salesData, $summary, $startDate, $endDate, $money) {
            yield ["Sales Report - {$startDate} to {$endDate}"];
            yield [];
            yield ['Date', 'Order #', 'Customer', 'Services', 'Add-ons', 'Discount', 'Total', 'Paid Amount', 'Outstanding', 'Status'];

            foreach ($salesData as $sale) {
                yield [
                    $sale['date'],
                    $sale['order_number'],
                    $sale['customer_name'],
                    $money($sale['items_total']),
                    $money($sale['addon_total']),
                    $money($sale['discount']),
                    $money($sale['total']),
                    $money($sale['paid_amount']),
                    $money($sale['outstanding']),
                    ucfirst($sale['status']),
                ];
            }

            yield [];
            yield ['Summary'];
            yield ['Total Orders', $summary['total_orders']];
            yield ['Total Sales', $money($summary['total_sales'])];
            yield ['Total Discount', $money($summary['total_discount'])];
            yield ['Total Paid', $money($summary['total_paid'])];
            yield ['Total Outstanding', $money($summary['total_outstanding'])];
            yield ['Total Items Sold', $summary['total_items_sold']];
            yield ['Total Add-ons Sold', $summary['total_addons_sold']];
            yield ['Average Order Value', $money($summary['average_order_value'])];
            yield ['Completed Orders', $summary['completed_orders']];
            yield ['Completion Rate', number_format($summary['completion_rate'], 2) . '%'];
            yield [];
            yield ['Generated on', now()->format('Y-m-d H:i:s')];
        })();

        return $this->csvDownload("sales_report_{$startDate}_to_{$endDate}.csv", $rows);
    }

    public function apiData(Request $request)
    {
        [$startDate, $endDate] = $this->range($request);

        $sales = $this->getSalesData($startDate, $endDate)
            ->map(fn ($sale) => array_diff_key($sale, array_flip(['order', 'customer', 'items', 'addons'])));

        return response()->json([
            'success' => true,
            'data' => [
                'sales' => $sales->values(),
                'summary' => $this->getSummaryStats($startDate, $endDate),
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ],
            'message' => 'Sales report data retrieved successfully',
        ]);
    }

    private function range(Request $request): array
    {
        $start = $this->dateInput($request, 'start_date', date('Y-m-01'));
        $end = $this->dateInput($request, 'end_date', date('Y-m-d'));

        return $start > $end ? [$end, $start] : [$start, $end];
    }

    private function getSalesData(string $startDate, string $endDate)
    {
        return Order::with(['customer:id,name', 'items.service:id,name', 'addons:id,name'])
            ->betweenDates($startDate, $endDate)
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->get()
            ->map(function ($order) {
                $itemsTotal = (float) $order->items->sum('total');
                $addonTotal = (float) $order->addons->sum('pivot.price');

                return [
                    'order' => $order,
                    'date' => $order->order_date?->format('Y-m-d'),
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer->name ?? 'Walk-in Customer',
                    'customer' => $order->customer,
                    'items_total' => $itemsTotal,
                    'addon_total' => $addonTotal,
                    'subtotal' => $itemsTotal + $addonTotal,
                    'discount' => (float) $order->discount,
                    'total' => (float) $order->total,
                    'paid_amount' => (float) $order->paid_amount,
                    'outstanding' => $order->balance,
                    'status' => $order->status,
                    'items' => $order->items,
                    'addons' => $order->addons,
                ];
            });
    }

    private function getSummaryStats(string $startDate, string $endDate): array
    {
        $base = fn () => Order::betweenDates($startDate, $endDate)->where('status', '!=', 'cancelled');

        $row = $base()
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('COALESCE(SUM(total), 0) as total_sales')
            ->selectRaw('COALESCE(SUM(discount), 0) as total_discount')
            ->selectRaw('COALESCE(SUM(paid_amount), 0) as total_paid')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders")
            ->first();

        $orderIds = $base()->select('id');

        $totalItemsSold = (int) DB::table('order_items')->whereIn('order_id', $orderIds)->sum('qty');
        $totalAddonsSold = (int) DB::table('order_addons')->whereIn('order_id', $base()->select('id'))->count();

        $totalOrders = (int) $row->total_orders;
        $completed = (int) $row->completed_orders;

        return [
            'total_orders' => $totalOrders,
            'total_sales' => (float) $row->total_sales,
            'total_discount' => (float) $row->total_discount,
            'total_paid' => (float) $row->total_paid,
            'total_outstanding' => max(0, (float) $row->total_sales - (float) $row->total_paid),
            'total_items_sold' => $totalItemsSold,
            'total_addons_sold' => $totalAddonsSold,
            'average_order_value' => $totalOrders > 0 ? (float) $row->total_sales / $totalOrders : 0,
            'completed_orders' => $completed,
            'completion_rate' => $totalOrders > 0 ? $completed / $totalOrders * 100 : 0,
        ];
    }
}
