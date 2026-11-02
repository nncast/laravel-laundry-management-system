<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\Format;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    /** Periods up to this long show their totals with the page; longer ones load them after it. */
    private const INLINE_SUMMARY_DAYS = 92;

    private function rangeLabel(string $start, string $end): string
    {
        return $start === $end ? fmt_date($start) : fmt_date($start) . ' – ' . fmt_date($end);
    }

    public function index(Request $request)
    {
        [$startDate, $endDate] = $this->range($request);
        $totals = fn () => [
            'summary' => $this->getSummaryStats($startDate, $endDate),
            'otherCurrencies' => Order::otherCurrencyTotals($this->billable($startDate, $endDate)),
            'range' => $this->rangeLabel($startDate, $endDate),
        ];

        // Just the totals (fetched by the page for long periods)
        if ($request->query('part') === 'summary') {
            return view('partials.sales-summary', $totals());
        }

        // Over a few months the totals take a moment: the page comes first and they follow
        $later = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) > self::INLINE_SUMMARY_DAYS;

        return view('report-sales', [
            // A page at a time: a year can hold tens of thousands of orders
            'salesData' => $this->salesQuery($startDate, $endDate)->orderByDesc('order_date')->orderByDesc('id')->paginate(25)->withQueryString()
                ->through(fn (Order $order) => $this->saleRow($order)),
            'summaryUrl' => $later ? $request->fullUrlWithQuery(['part' => 'summary']) : null,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ] + ($later ? ['summary' => null, 'otherCurrencies' => null, 'range' => $this->rangeLabel($startDate, $endDate)] : $totals()));
    }

    public function download(Request $request)
    {
        [$startDate, $endDate] = $this->range($request);

        $orders = $this->orderExportRows($this->salesQuery($startDate, $endDate));
        $summary = $this->getSummaryStats($startDate, $endDate);
        $money = fn ($v) => number_format((float) $v, 2, '.', '');
        $currency = app(Format::class)->currency;

        $rows = (function () use ($orders, $summary, $startDate, $endDate, $money, $currency) {
            yield ["Sales Report - {$startDate} to {$endDate}"];
            yield [];
            yield ['Date', 'Order #', 'Customer', 'Currency', 'Services', 'Add-ons', 'Discount', 'Total', 'Paid Amount', 'Outstanding', 'Status'];

            foreach ($orders as $order) {
                yield [
                    substr((string) $order->order_date, 0, 10),
                    $order->order_number,
                    $order->customer_name ?? 'Walk-in Customer',
                    $order->currency,
                    $money($order->subtotal),
                    $money($order->addon_total),
                    $money($order->discount),
                    $money($order->total),
                    $money($order->paid_amount),
                    $money((float) $order->total - (float) $order->paid_amount),
                    Order::labelFor($order->status),
                ];
            }

            yield [];
            yield ["Summary ({$currency} orders)"];
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

        $sales = $this->salesQuery($startDate, $endDate)->orderByDesc('order_date')->orderByDesc('id')->paginate(100)
            ->through(fn (Order $order) => array_diff_key($this->saleRow($order), array_flip(['order', 'customer', 'items', 'addons'])));

        return response()->json([
            'success' => true,
            'data' => [
                'sales' => $sales->items(),
                'page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
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

    private function salesQuery(string $startDate, string $endDate)
    {
        return Order::with(['customer:id,name', 'items.service:id,name', 'addons:id,name'])
            ->betweenDates($startDate, $endDate)
            ->where('status', '!=', 'cancelled');
    }

    private function saleRow(Order $order): array
    {
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
    }

    /** Orders that count as sales: not cancelled (listed, so the status index can be used). */
    private function billable(string $startDate, string $endDate)
    {
        return Order::betweenDates($startDate, $endDate)->whereIn('status', array_diff(Order::STATUSES, ['cancelled']));
    }

    /**
     * Totals for the period. A period that ended before today hardly changes, so its
     * totals are kept for a few minutes; one that includes today is always live.
     */
    private function getSummaryStats(string $startDate, string $endDate): array
    {
        if ($endDate >= today()->toDateString()) {
            return $this->summaryStats($startDate, $endDate);
        }

        $key = 'report.sales.' . app(Format::class)->currency . ".{$startDate}.{$endDate}";

        return Cache::remember($key, now()->addMinutes(5), fn () => $this->summaryStats($startDate, $endDate));
    }

    private function summaryStats(string $startDate, string $endDate): array
    {
        $base = fn () => $this->billable($startDate, $endDate)->inBusinessCurrency();

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
