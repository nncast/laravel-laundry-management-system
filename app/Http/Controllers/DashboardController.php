<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Support\Format;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /** Automatic backups run every 24 hours; one older than this means they are failing. */
    private const BACKUP_STALE_HOURS = 26;

    /** The action center lists this many orders per tab (the tab shows the full count). */
    private const ATTENTION_ROWS = 6;

    /** Trends (chart, top services) may be this many minutes old; the counts are always live. */
    private const TREND_CACHE_MINUTES = 10;

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

        [$topServices, $chartData] = $this->trends();

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
     * backups that have stopped happening. Each list holds the oldest few;
     * the *Count values say how many there are in all.
     */
    private function attention(): array
    {
        $lowStock = Product::where('status', 'active')
            ->whereColumn('available_stock', '<=', 'minimum_stock_level')
            ->orderBy('available_stock')
            ->get(['id', 'name', 'available_stock', 'minimum_stock_level']);

        $first = fn ($query) => (clone $query)->with('customer:id,name')->orderBy('order_date')->take(self::ATTENTION_ROWS)->get();

        $overdue = Order::overdue();
        $ready = Order::where('status', 'ready');
        $pickedUpUnpaid = Order::where('status', 'completed')->whereColumn('paid_amount', '<', 'total');

        $stuckRule = __('Pending :pending+ day or processing :processing+ days', ['pending' => Order::OVERDUE_PENDING_DAYS, 'processing' => Order::OVERDUE_PROCESSING_DAYS]);

        $isAdmin = session('staff.role') === 'admin';
        $lastBackupAt = $isAdmin ? BackupController::lastBackupTime() : null;
        $backupStale = $isAdmin && ($lastBackupAt === null || $lastBackupAt < time() - self::BACKUP_STALE_HOURS * 3600);

        return [
            'lowStock' => $lowStock,
            'stuckOrders' => $first($overdue),
            'stuckCount' => (clone $overdue)->count(),
            'readyOrders' => $first($ready),
            'readyListCount' => (clone $ready)->count(),
            'unpaidOrders' => $first($pickedUpUnpaid),
            'unpaidPickupCount' => (clone $pickedUpUnpaid)->count(),
            'stuckRule' => $stuckRule,
            'lastBackupAt' => $lastBackupAt,
            'backupStale' => $backupStale,
        ];
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
     * Headline numbers. Each query filters on status first, so it is answered
     * from orders_status_date_money_index without reading whole rows.
     */
    private function stats(): array
    {
        // Money sums count the business currency only (see Order::scopeInBusinessCurrency)
        $currency = app(Format::class)->currency;

        $open = Order::whereIn('status', Order::OPEN_STATUSES)
            ->selectRaw('status, COUNT(*) as n')
            ->groupBy('status')
            ->pluck('n', 'status');

        $unpaid = Order::where('status', '!=', 'cancelled')
            ->where('currency', $currency)
            ->whereColumn('paid_amount', '<', 'total')
            ->selectRaw('COUNT(*) as n, COALESCE(SUM(total - paid_amount), 0) as amount_due')   // not "balance": Order has a balance accessor
            ->first();

        $today = Order::today()
            ->selectRaw("SUM(CASE WHEN status <> 'cancelled' THEN 1 ELSE 0 END) as orders")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' AND currency = ? THEN total ELSE 0 END), 0) as revenue", [$currency])
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->selectRaw("SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->first();

        $pending = (int) ($open['pending'] ?? 0);
        $processing = (int) ($open['processing'] ?? 0);
        $ready = (int) ($open['ready'] ?? 0);

        return [
            'totalOrders' => Order::count(),
            'pendingOrders' => $pending,
            'processingOrders' => $processing,
            'readyCount' => $ready,
            'openOrders' => $pending + $processing + $ready,
            'overdueCount' => Order::overdue()->count(),
            'unpaidCount' => (int) $unpaid->n,
            'unpaidBalance' => round((float) $unpaid->amount_due, 2),
            'totalRevenue' => (float) Order::where('status', 'completed')->where('currency', $currency)->sum('total'),
            'todayOrders' => (int) $today->orders,
            'todayRevenue' => (float) $today->revenue,
            'todayCompleted' => (int) $today->completed,
            'todayProcessing' => (int) $today->processing,
            'todayPending' => (int) $today->pending,
        ];
    }

    /**
     * Top services and the sales chart: trends, so a few minutes' delay is fine
     * and the heavier grouped queries run at most once per period.
     */
    private function trends(): array
    {
        $key = 'dashboard.trends.' . app(Format::class)->currency . '.' . app()->getLocale() . '.' . today()->toDateString();

        return Cache::remember($key, now()->addMinutes(self::TREND_CACHE_MINUTES), function () {
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

            return [$topServices, $chartData];
        });
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
                'month' => [__('Week :number', ['number' => intdiv($date->day - 1, 7) + 1]), __('Week :number of :month', ['number' => intdiv($date->day - 1, 7) + 1, 'month' => $date->translatedFormat('F')])],
                'year' => [$date->translatedFormat('M'), $date->translatedFormat('F Y')],
                default => [$date->translatedFormat('D'), fmt_date($date)],
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
