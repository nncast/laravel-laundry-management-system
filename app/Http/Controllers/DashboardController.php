<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Support\Format;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /** Automatic backups run every 24 hours; one older than this means they are failing. */
    private const BACKUP_STALE_HOURS = 26;

    /**
     * Display the dashboard.
     */
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $startOfLastMonth = $startOfMonth->copy()->subMonthNoOverflow();

        // Compare this month so far with the SAME days of last month
        // (e.g. Oct 1-4 vs Sep 1-4), otherwise early in the month every
        // trend would look like a large drop.
        $sameDayLastMonth = $startOfLastMonth->copy()
            ->addDays($today->day - 1)
            ->min($startOfLastMonth->copy()->endOfMonth()->startOfDay());

        $thisMonth = $this->periodTotals($startOfMonth, $today);
        $lastMonth = $this->periodTotals($startOfLastMonth, $sameDayLastMonth);
        $monthRevenue = $thisMonth->revenue;
        $revenueTrend = $this->percentChange($thisMonth->revenue, $lastMonth->revenue);

        // Money taken at the counter today, whatever day the order is from
        $collectedToday = (float) Payment::join('orders', 'orders.id', '=', 'payments.order_id')
            ->where('payments.created_at', '>=', $today)
            ->where('orders.currency', app(Format::class)->currency)
            ->sum('payments.amount');

        $recentOrders = Order::with('customer:id,name')
            ->latest('id')
            ->take(8)
            ->get();

        // Top services by number of completed orders
        $topServices = Service::query()
            ->select(
                'services.id',
                'services.name',
                DB::raw('COUNT(order_items.id) as order_count'),
                DB::raw('COALESCE(SUM(order_items.total), 0) as total_revenue')
            )
            ->join('order_items', 'services.id', '=', 'order_items.service_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('orders.currency', app(Format::class)->currency)
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('order_count')
            ->take(5)
            ->get();

        $chartData = [
            'week' => $this->chartData('week'),
            'month' => $this->chartData('month'),
            'year' => $this->chartData('year'),
        ];

        // Automatic daily backup, made after this page has been sent so nobody waits for it
        app()->terminating(fn () => BackupController::backupIfDue());

        return view('dashboard', array_merge($this->stats(), $this->attention(), compact(
            'monthRevenue',
            'revenueTrend',
            'collectedToday',
            'recentOrders',
            'topServices',
            'chartData'
        )));
    }

    /**
     * Things staff should act on, found automatically on every dashboard load:
     * overdue orders, orders ready for pickup, completed orders that still have
     * a balance, products at or below their minimum stock and, for admins,
     * backups that have stopped happening.
     */
    private function attention(): array
    {
        $lowStock = Product::where('status', 'active')
            ->whereColumn('available_stock', '<=', 'minimum_stock_level')
            ->orderBy('available_stock')
            ->get(['id', 'name', 'available_stock', 'minimum_stock_level']);

        $stuckOrders = Order::with('customer:id,name')->overdue()->orderBy('order_date')->get();

        $readyOrders = Order::with('customer:id,name')->where('status', 'ready')->orderBy('order_date')->get();

        $unpaidOrders = Order::with('customer:id,name')
            ->where('status', 'completed')
            ->whereColumn('paid_amount', '<', 'total')
            ->orderBy('order_date')
            ->get();

        $stuckRule = 'Pending ' . Order::OVERDUE_PENDING_DAYS . '+ day or processing ' . Order::OVERDUE_PROCESSING_DAYS . '+ days';

        $isAdmin = session('staff.role') === 'admin';
        $lastBackupAt = $isAdmin ? BackupController::lastBackupTime() : null;
        $backupStale = $isAdmin && ($lastBackupAt === null || $lastBackupAt < time() - self::BACKUP_STALE_HOURS * 3600);

        return compact('lowStock', 'stuckOrders', 'readyOrders', 'unpaidOrders', 'stuckRule', 'lastBackupAt', 'backupStale');
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
     * Headline numbers, computed with a few aggregate queries.
     */
    private function stats(): array
    {
        // Money sums count the business currency only (see Order::scopeInBusinessCurrency)
        $currency = app(Format::class)->currency;

        $overall = Order::query()
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders")
            ->selectRaw("SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders")
            ->selectRaw("SUM(CASE WHEN status = 'ready' THEN 1 ELSE 0 END) as ready_orders")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' AND currency = ? THEN total ELSE 0 END), 0) as total_revenue", [$currency])
            ->selectRaw("SUM(CASE WHEN status <> 'cancelled' AND paid_amount < total AND currency = ? THEN 1 ELSE 0 END) as unpaid_count", [$currency])
            ->selectRaw("COALESCE(SUM(CASE WHEN status <> 'cancelled' AND paid_amount < total AND currency = ? THEN total - paid_amount ELSE 0 END), 0) as unpaid_balance", [$currency])
            ->first();

        $today = Order::today()
            ->selectRaw("SUM(CASE WHEN status <> 'cancelled' THEN 1 ELSE 0 END) as orders")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' AND currency = ? THEN total ELSE 0 END), 0) as revenue", [$currency])
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->first();

        return [
            'totalOrders' => (int) $overall->total_orders,
            'pendingOrders' => (int) $overall->pending_orders,
            'processingOrders' => (int) $overall->processing_orders,
            'readyCount' => (int) $overall->ready_orders,
            'openOrders' => (int) $overall->pending_orders + (int) $overall->processing_orders + (int) $overall->ready_orders,
            'overdueCount' => Order::overdue()->count(),
            'unpaidCount' => (int) $overall->unpaid_count,
            'unpaidBalance' => round((float) $overall->unpaid_balance, 2),
            'totalRevenue' => (float) $overall->total_revenue,
            'todayOrders' => (int) $today->orders,
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
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' AND currency = ? THEN total ELSE 0 END), 0) as revenue", [app(Format::class)->currency])
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
            ->inBusinessCurrency()
            ->selectRaw('substr(order_date, 1, 10) as day, COUNT(*) as orders, SUM(total) as sales')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        // Bucket the daily figures into the period's labels; "titles" name each bucket in full
        $buckets = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            [$label, $title] = match ($period) {
                'month' => ['Week ' . (intdiv($date->day - 1, 7) + 1), 'Week ' . (intdiv($date->day - 1, 7) + 1) . ' of ' . $date->format('F')],
                'year' => [$date->format('M'), $date->format('F Y')],
                default => [$date->format('D'), $date->format('l, M j')],
            };

            $buckets[$label] ??= ['title' => $title, 'sales' => 0.0, 'orders' => 0];

            if ($row = $daily->get($date->toDateString())) {
                $buckets[$label]['sales'] += (float) $row->sales;
                $buckets[$label]['orders'] += (int) $row->orders;
            }
        }

        return [
            'labels' => array_keys($buckets),
            'titles' => array_column($buckets, 'title'),
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
