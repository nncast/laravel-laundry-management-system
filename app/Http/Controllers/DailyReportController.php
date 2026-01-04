<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Get date from request or default to today
            $date = $request->input('date', date('Y-m-d'));
            
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $date = date('Y-m-d');
            }
            
            // Get daily statistics
            $stats = $this->getDailyStats($date);
            
            // Return view with data
            return view('report-daily', [
                'selectedDate' => $date,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            // Log error and return with default stats
            \Log::error('Daily Report Error: ' . $e->getMessage());
            
            return view('report-daily', [
                'selectedDate' => date('Y-m-d'),
                'stats' => $this->getDefaultStats(),
                'error' => 'Unable to load report data. Please try again.'
            ]);
        }
    }
    
    private function getDailyStats($date)
    {
        // Get orders for the selected date
        $orders = Order::whereDate('order_date', $date)->get();
        
        // Calculate total orders
        $totalOrders = $orders->count();
        
        // Calculate completed orders
        $completedOrders = $orders->where('status', 'completed')->count();
        
        // Calculate total sales (from orders total field)
        $totalSales = $orders->sum('total');
        
        // Calculate total payments for the date - FIXED: using payments table
        $totalPayments = Payment::whereHas('order', function ($query) use ($date) {
            $query->whereDate('order_date', $date);
        })->sum('amount');
        
        // If payments table is empty or has issues, fall back to orders.paid_amount
        if ($totalPayments == 0 && $orders->sum('paid_amount') > 0) {
            $totalPayments = $orders->sum('paid_amount');
        }
        
        // Calculate paid amount from orders
        $paidAmount = $orders->sum('paid_amount');
        
        // Calculate outstanding amount
        $outstanding = max(0, $totalSales - $paidAmount);
        
        // Get order breakdown by status
        $statusBreakdown = $orders->groupBy('status')->map->count();
        
        // Get top services for the day
        $topServices = DB::table('order_items')
            ->join('services', 'order_items.service_id', '=', 'services.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereDate('orders.order_date', $date)
            ->select('services.name', DB::raw('SUM(order_items.qty) as total_qty'), DB::raw('SUM(order_items.total) as total_amount'))
            ->groupBy('services.id', 'services.name') // Group by id and name for better compatibility
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();
        
        return [
            'date' => $date,
            'total_orders' => $totalOrders,
            'delivered_orders' => $completedOrders, // FIXED: Changed from 'completed_orders' to 'delivered_orders'
            'total_sales' => $totalSales,
            'total_payments' => $totalPayments,
            'paid_amount' => $paidAmount,
            'outstanding' => $outstanding,
            'status_breakdown' => $statusBreakdown,
            'top_services' => $topServices,
            'orders' => $orders
        ];
    }
    
    private function getDefaultStats()
    {
        return [
            'date' => date('Y-m-d'),
            'total_orders' => 0,
            'delivered_orders' => 0, // FIXED: Changed from 'completed_orders' to 'delivered_orders'
            'total_sales' => 0,
            'total_payments' => 0,
            'paid_amount' => 0,
            'outstanding' => 0,
            'status_breakdown' => [],
            'top_services' => collect([]),
            'orders' => collect([])
        ];
    }
    
    public function download(Request $request)
    {
        try {
            $date = $request->input('date', date('Y-m-d'));
            
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $date = date('Y-m-d');
            }
            
            $stats = $this->getDailyStats($date);
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Report data retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Daily Report Download Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'data' => $this->getDefaultStats(),
                'message' => 'Error generating report. Please try again.'
            ], 500);
        }
    }
}