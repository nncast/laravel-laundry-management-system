<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();
        
        // Total Orders
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        
        // Revenue calculations
        $totalRevenue = Order::where('status', 'completed')->sum('total');
        $todayRevenue = Order::whereDate('order_date', $today)
            ->where('status', 'completed')
            ->sum('total');
        
        // Today's statistics
        $todayOrders = Order::whereDate('order_date', $today)->count();
        $todayPending = Order::whereDate('order_date', $today)
            ->where('status', 'pending')
            ->count();
        $todayProcessing = Order::whereDate('order_date', $today)
            ->where('status', 'processing')
            ->count();
        $todayCompleted = Order::whereDate('order_date', $today)
            ->where('status', 'completed')
            ->count();
        
        // Recent Orders (last 10)
        $recentOrders = Order::with(['customer', 'items.service'])
            ->latest()
            ->take(10)
            ->get();
        
        // Top Services by order count
        $topServices = Service::select(
                'services.id',
                'services.name',
                'services.price', // ADD THIS LINE
                DB::raw('COUNT(order_items.id) as order_count'),
                DB::raw('SUM(order_items.total) as total_revenue')
            )
            ->leftJoin('order_items', 'services.id', '=', 'order_items.service_id')
            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->groupBy('services.id', 'services.name', 'services.price') // ADD 'services.price' here
            ->orderByDesc('order_count')
            ->take(10)
            ->get();
        
        // Weekly sales data for chart
        $weeklySales = [];
        $weeklyOrders = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayName = $date->format('D');
            
            $sales = Order::whereDate('order_date', $date)
                ->where('status', 'completed')
                ->sum('total');
            
            $orders = Order::whereDate('order_date', $date)
                ->where('status', 'completed')
                ->count();
            
            $weeklySales[$dayName] = $sales;
            $weeklyOrders[$dayName] = $orders;
        }
        
        // Monthly sales data
        $monthlySales = [];
        $monthlyOrders = [];
        
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        for ($week = 1; $week <= 4; $week++) {
            $weekStart = Carbon::create($currentYear, $currentMonth, ($week - 1) * 7 + 1);
            $weekEnd = Carbon::create($currentYear, $currentMonth, min($week * 7, 30));
            
            $sales = Order::whereBetween('order_date', [$weekStart, $weekEnd])
                ->where('status', 'completed')
                ->sum('total');
            
            $orders = Order::whereBetween('order_date', [$weekStart, $weekEnd])
                ->where('status', 'completed')
                ->count();
            
            $monthlySales["Week $week"] = $sales;
            $monthlyOrders["Week $week"] = $orders;
        }
        
        // Yearly sales data
        $yearlySales = [];
        $yearlyOrders = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $monthName = Carbon::create($currentYear, $month, 1)->format('M');
            
            $sales = Order::whereMonth('order_date', $month)
                ->whereYear('order_date', $currentYear)
                ->where('status', 'completed')
                ->sum('total');
            
            $orders = Order::whereMonth('order_date', $month)
                ->whereYear('order_date', $currentYear)
                ->where('status', 'completed')
                ->count();
            
            $yearlySales[$monthName] = $sales;
            $yearlyOrders[$monthName] = $orders;
        }
        
        // Chart data
        $chartData = [
            'week' => [
                'labels' => array_keys($weeklySales),
                'sales' => array_values($weeklySales),
                'orders' => array_values($weeklyOrders)
            ],
            'month' => [
                'labels' => array_keys($monthlySales),
                'sales' => array_values($monthlySales),
                'orders' => array_values($monthlyOrders)
            ],
            'year' => [
                'labels' => array_keys($yearlySales),
                'sales' => array_values($yearlySales),
                'orders' => array_values($yearlyOrders)
            ]
        ];
        
        // Calculate trends
        $lastMonth = Carbon::now()->subMonth();
        $lastMonthRevenue = Order::whereMonth('order_date', $lastMonth->month)
            ->whereYear('order_date', $lastMonth->year)
            ->where('status', 'completed')
            ->sum('total');
        
        $revenueTrend = $lastMonthRevenue > 0 
            ? (($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue * 100)
            : 0;
        
        $lastMonthOrders = Order::whereMonth('order_date', $lastMonth->month)
            ->whereYear('order_date', $lastMonth->year)
            ->count();
        
        $ordersTrend = $lastMonthOrders > 0
            ? (($totalOrders - $lastMonthOrders) / $lastMonthOrders * 100)
            : 0;
        
        $yesterday = Carbon::yesterday();
        $yesterdayPending = Order::whereDate('order_date', $yesterday)
            ->where('status', 'pending')
            ->count();
        
        $pendingTrend = $yesterdayPending > 0
            ? (($pendingOrders - $yesterdayPending) / $yesterdayPending * 100)
            : 0;
        
        return view('dashboard', compact(
            'totalOrders',
            'pendingOrders',
            'totalRevenue',
            'todayRevenue',
            'todayOrders',
            'todayPending',
            'todayProcessing',
            'todayCompleted',
            'recentOrders',
            'topServices',
            'chartData',
            'revenueTrend',
            'ordersTrend',
            'pendingTrend'
        ));
    }
    
    /**
     * Get dashboard stats for AJAX requests.
     */
    public function getStats()
    {
        $today = Carbon::today();
        
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalRevenue = Order::where('status', 'completed')->sum('total');
        $todayRevenue = Order::whereDate('order_date', $today)
            ->where('status', 'completed')
            ->sum('total');
        
        // Today's stats for income card
        $todayCompleted = Order::whereDate('order_date', $today)
            ->where('status', 'completed')
            ->count();
        $todayProcessing = Order::whereDate('order_date', $today)
            ->where('status', 'processing')
            ->count();
        $todayPending = Order::whereDate('order_date', $today)
            ->where('status', 'pending')
            ->count();
        
        return response()->json([
            'success' => true,
            'stats' => [
                'totalOrders' => $totalOrders,
                'pendingOrders' => $pendingOrders,
                'totalRevenue' => $totalRevenue,
                'todayRevenue' => $todayRevenue,
                'todayCompleted' => $todayCompleted,
                'todayProcessing' => $todayProcessing,
                'todayPending' => $todayPending
            ]
        ]);
    }
    
    /**
     * Get chart data for specific period.
     */
    public function getChartData($period)
    {
        $data = [];
        
        if ($period === 'week') {
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $dayName = $date->format('D');
                
                $sales = Order::whereDate('order_date', $date)
                    ->where('status', 'completed')
                    ->sum('total');
                
                $orders = Order::whereDate('order_date', $date)
                    ->where('status', 'completed')
                    ->count();
                
                $data['labels'][] = $dayName;
                $data['sales'][] = $sales;
                $data['orders'][] = $orders;
            }
        } elseif ($period === 'month') {
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            
            for ($week = 1; $week <= 4; $week++) {
                $weekStart = Carbon::create($currentYear, $currentMonth, ($week - 1) * 7 + 1);
                $weekEnd = Carbon::create($currentYear, $currentMonth, min($week * 7, 30));
                
                $sales = Order::whereBetween('order_date', [$weekStart, $weekEnd])
                    ->where('status', 'completed')
                    ->sum('total');
                
                $orders = Order::whereBetween('order_date', [$weekStart, $weekEnd])
                    ->where('status', 'completed')
                    ->count();
                
                $data['labels'][] = "Week $week";
                $data['sales'][] = $sales;
                $data['orders'][] = $orders;
            }
        } elseif ($period === 'year') {
            $currentYear = Carbon::now()->year;
            
            for ($month = 1; $month <= 12; $month++) {
                $monthName = Carbon::create($currentYear, $month, 1)->format('M');
                
                $sales = Order::whereMonth('order_date', $month)
                    ->whereYear('order_date', $currentYear)
                    ->where('status', 'completed')
                    ->sum('total');
                
                $orders = Order::whereMonth('order_date', $month)
                    ->whereYear('order_date', $currentYear)
                    ->where('status', 'completed')
                    ->count();
                
                $data['labels'][] = $monthName;
                $data['sales'][] = $sales;
                $data['orders'][] = $orders;
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}