<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $date = $this->dateInput($request, 'date', date('Y-m-d'));

        try {
            $stats = $this->getDailyStats($date);
        } catch (\Throwable $e) {
            Log::error('Daily Report Error: ' . $e->getMessage());

            return view('report-daily', [
                'selectedDate' => $date,
                'stats' => $this->getDefaultStats($date),
                'error' => 'Unable to load report data. Please try again.',
            ]);
        }

        return view('report-daily', [
            'selectedDate' => $date,
            'stats' => $stats,
        ]);
    }

    public function download(Request $request)
    {
        $date = $this->dateInput($request, 'date', date('Y-m-d'));

        try {
            $stats = $this->getDailyStats($date);
        } catch (\Throwable $e) {
            Log::error('Daily Report Download Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'data' => $this->getDefaultStats($date),
                'message' => 'Error generating report. Please try again.',
            ], 500);
        }

        unset($stats['orders']);

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Report data retrieved successfully',
        ]);
    }

    private function getDailyStats(string $date): array
    {
        $orders = Order::with('customer:id,name')
            ->onDate($date)
            ->orderBy('id')
            ->get();

        // Cancelled orders are not sales
        $billable = $orders->where('status', '!=', 'cancelled');

        $totalSales = (float) $billable->sum('total');
        $paidAmount = (float) $billable->sum('paid_amount');

        // Money actually received on this day (for any order)
        $totalPayments = (float) Payment::whereDate('created_at', $date)->sum('amount');

        $topServices = DB::table('order_items')
            ->join('services', 'order_items.service_id', '=', 'services.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.order_date', '>=', $date)
            ->where('orders.order_date', '<', date('Y-m-d', strtotime($date . ' +1 day')))
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'services.name',
                DB::raw('SUM(order_items.qty) as total_qty'),
                DB::raw('SUM(order_items.total) as total_amount')
            )
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        return [
            'date' => $date,
            'total_orders' => $orders->count(),
            'delivered_orders' => $orders->where('status', 'completed')->count(),
            'total_sales' => $totalSales,
            'total_payments' => $totalPayments,
            'paid_amount' => $paidAmount,
            'outstanding' => max(0, round($totalSales - $paidAmount, 2)),
            'status_breakdown' => $orders->groupBy('status')->map->count(),
            'top_services' => $topServices,
            'orders' => $orders,
        ];
    }

    private function getDefaultStats(string $date): array
    {
        return [
            'date' => $date,
            'total_orders' => 0,
            'delivered_orders' => 0,
            'total_sales' => 0,
            'total_payments' => 0,
            'paid_amount' => 0,
            'outstanding' => 0,
            'status_breakdown' => collect(),
            'top_services' => collect(),
            'orders' => collect(),
        ];
    }
}
