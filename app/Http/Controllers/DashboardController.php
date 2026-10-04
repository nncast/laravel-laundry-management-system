<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfMonth = $today->copy()->startOfMonth();
        $startOfLastMonth = $startOfMonth->copy()->subMonthNoOverflow();

        // Compare this month so far with the SAME days of last month
        // (e.g. Oct 1-4 vs Sep 1-4), otherwise early in the month every
        // trend would look like a large drop.
        $sameDayLastMonth = $startOfLastMonth->copy()
            ->addDays($today->day - 1)
            ->min($startOfLastMonth->copy()->endOfMonth()->startOfDay());

        $stats = $this->stats();

        $thisMonth = $this->periodTotals($startOfMonth, $today);
        $lastMonth = $this->periodTotals($startOfLastMonth, $sameDayLastMonth);

        $revenueTrend = $this->percentChange($thisMonth->revenue, $lastMonth->revenue);
        $ordersTrend = $this->percentChange($thisMonth->orders, $lastMonth->orders);

        // Pending orders created today vs. yesterday
        $yesterdayPending = Order::onDate($yesterday)->where('status', 'pending')->count();
        $pendingTrend = $this->percentChange($stats['todayPending'], $yesterdayPending);

        // Recent Orders (last 10)
        $recentOrders = Order::with('customer:id,name')
            ->latest('id')
            ->take(10)
            ->get();

        // Top Services by number of completed orders
        $topServices = Service::query()
            ->select(
                'services.id',
                'services.name',
                'services.price',
                DB::raw('COUNT(order_items.id) as order_count'),
                DB::raw('COALESCE(SUM(order_items.total), 0) as total_revenue')
            )
            ->join('order_items', 'services.id', '=', 'order_items.service_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->groupBy('services.id', 'services.name', 'services.price')
            ->orderByDesc('order_count')
            ->take(10)
            ->get();

        $chartData = [
            'week' => $this->chartData('week'),
            'month' => $this->chartData('month'),
            'year' => $this->chartData('year'),
        ];

        return view('dashboard', array_merge($stats, compact(
            'recentOrders',
            'topServices',
            'chartData',
            'revenueTrend',
            'ordersTrend',
            'pendingTrend'
        )));
    }

    /**
     * Get dashboard stats for AJAX requests.
     */
    public function getStats()
    {
        return response()->json([
            'success' => true,
            'stats' => $this->stats(),
        ]);
    }

    /**
     * Get chart data for specific period.
     */
    public function getChartData(string $period)
    {
        return response()->json([
            'success' => true,
            'data' => $this->chartData($period),
        ]);
    }

    /**
     * Headline numbers, computed with two aggregate queries.
     */
    private function stats(): array
    {
        $overall = Order::query()
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END), 0) as total_revenue")
            ->first();

        $today = Order::today()
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END), 0) as revenue")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->first();

        return [
            'totalOrders' => (int) $overall->total_orders,
            'pendingOrders' => (int) $overall->pending_orders,
            'totalRevenue' => (float) $overall->total_revenue,
            'todayRevenue' => (float) $today->revenue,
            'todayCompleted' => (int) $today->completed,
            'todayProcessing' => (int) $today->processing,
            'todayPending' => (int) $today->pending,
        ];
    }

    /**
     * Completed revenue and order count for a date range.
     */
    private function periodTotals(Carbon $start, Carbon $end): object
    {
        $row = Order::betweenDates($start, $end)
            ->selectRaw('COUNT(*) as orders')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END), 0) as revenue")
            ->first();

        return (object) ['orders' => (int) $row->orders, 'revenue' => (float) $row->revenue];
    }

    /**
     * Chart series for "week" (last 7 days), "month" (weeks of this month) or
     * "year" (months of this year), built from ONE grouped query.
     */
    private function chartData(string $period): array
    {
        $today = Carbon::today();

        [$start, $end] = match ($period) {
            'month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()->startOfDay()],
            'year' => [$today->copy()->startOfYear(), $today->copy()->endOfYear()->startOfDay()],
            default => [$today->copy()->subDays(6), $today->copy()],
        };

        $daily = Order::betweenDates($start, $end)
            ->where('status', 'completed')
            ->selectRaw('substr(order_date, 1, 10) as day, COUNT(*) as orders, SUM(total) as sales')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        // Bucket the daily figures into the period's labels
        $buckets = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $label = match ($period) {
                'month' => 'Week ' . (intdiv($date->day - 1, 7) + 1),
                'year' => $date->format('M'),
                default => $date->format('D'),
            };

            $buckets[$label] ??= ['sales' => 0.0, 'orders' => 0];

            if ($row = $daily->get($date->toDateString())) {
                $buckets[$label]['sales'] += (float) $row->sales;
                $buckets[$label]['orders'] += (int) $row->orders;
            }
        }

        return [
            'labels' => array_keys($buckets),
            'sales' => array_map(fn ($b) => round($b['sales'], 2), array_values($buckets)),
            'orders' => array_column($buckets, 'orders'),
        ];
    }

    private function percentChange(float|int $current, float|int $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }
}
