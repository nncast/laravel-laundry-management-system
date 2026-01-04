<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderReportController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));
        $status = $request->input('status', '');
        
        // Validate dates
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = date('Y-m-01');
        }
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = date('Y-m-t');
        }
        
        // Get orders with filters
        $orders = $this->getFilteredOrders($startDate, $endDate, $status);
        
        // Get summary statistics
        $summary = $this->getSummaryStats($orders);
        
        // Prepare services list for filter
        $services = Service::where('is_active', 1)->get(['id', 'name']);
        
        return view('report-orders', [
            'orders' => $orders,
            'summary' => $summary,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status
            ],
            'services' => $services
        ]);
    }
    
    private function getFilteredOrders($startDate, $endDate, $status = '')
    {
        $query = Order::with(['customer', 'orderItems.service'])
            ->whereBetween('order_date', [$startDate, $endDate])
            ->orderBy('order_date', 'desc')
            ->orderBy('id', 'desc');
        
        // Apply status filter if provided
        if (!empty($status)) {
            $query->where('status', $status);
        }
        
        return $query->paginate(10);
    }
    
    private function getSummaryStats($orders)
    {
        // Get total orders count (including pagination)
        $totalOrders = $orders->total();
        
        // Calculate total amount from orders collection
        $totalAmount = $orders->sum('total');
        
        // Get counts by status from current page
        $completedCount = $orders->where('status', 'completed')->count();
        $pendingCount = $orders->where('status', 'pending')->count();
        $processingCount = $orders->where('status', 'processing')->count();
        $cancelledCount = $orders->where('status', 'cancelled')->count();
        
        // Get overall counts for the filter period (not just current page)
        $filteredQuery = Order::query();
        
        if (request()->has('start_date') && request()->has('end_date')) {
            $filteredQuery->whereBetween('order_date', [
                request('start_date'),
                request('end_date')
            ]);
        }
        
        if (request()->has('status') && !empty(request('status'))) {
            $filteredQuery->where('status', request('status'));
        }
        
        $allFilteredOrders = $filteredQuery->get();
        $overallTotalAmount = $allFilteredOrders->sum('total');
        $overallCompletedCount = $allFilteredOrders->where('status', 'completed')->count();
        $overallPendingCount = $allFilteredOrders->where('status', 'pending')->count();
        
        return [
            'total_orders' => $totalOrders,
            'total_amount' => $overallTotalAmount,
            'completed_count' => $overallCompletedCount,
            'pending_count' => $overallPendingCount,
            'processing_count' => $processingCount,
            'cancelled_count' => $cancelledCount,
            'page_orders' => $orders->count(),
            'page_amount' => $totalAmount
        ];
    }
    
    public function download(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));
        $status = $request->input('status', '');
        
        // Get all orders without pagination for download
        $orders = Order::with(['customer', 'orderItems.service'])
            ->whereBetween('order_date', [$startDate, $endDate])
            ->when(!empty($status), function($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('order_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        
        // Generate CSV content
        $csvContent = "Order Report - {$startDate} to {$endDate}\n\n";
        $csvContent .= "Order Number,Date,Customer,Service,Amount,Status\n";
        
        foreach ($orders as $order) {
            $serviceNames = $order->orderItems->map(function ($item) {
                return $item->service->name . ($item->qty > 1 ? ' (x' . $item->qty . ')' : '');
            })->implode(', ');
            
            $csvContent .= sprintf(
                '"%s","%s","%s","%s",%.2f,"%s"',
                $order->order_number,
                $order->order_date->format('Y-m-d'),
                $order->customer ? $order->customer->name : 'Walk-in Customer',
                $serviceNames,
                $order->total,
                ucfirst($order->status)
            ) . "\n";
        }
        
        // Summary
        $csvContent .= "\n\nSummary\n";
        $csvContent .= "Total Orders:," . $orders->count() . "\n";
        $csvContent .= "Total Amount:," . $orders->sum('total') . "\n";
        $csvContent .= "Completed:," . $orders->where('status', 'completed')->count() . "\n";
        $csvContent .= "Pending:," . $orders->where('status', 'pending')->count() . "\n";
        $csvContent .= "Processing:," . $orders->where('status', 'processing')->count() . "\n";
        $csvContent .= "Cancelled:," . $orders->where('status', 'cancelled')->count() . "\n";
        $csvContent .= "\nGenerated on: " . now()->format('Y-m-d H:i:s');
        
        $filename = "order_report_{$startDate}_to_{$endDate}.csv";
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    public function apiData(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));
        $status = $request->input('status', '');
        
        // Get orders for API/JSON response
        $orders = Order::with(['customer', 'orderItems.service'])
            ->whereBetween('order_date', [$startDate, $endDate])
            ->when(!empty($status), function($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('order_date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($order) {
                $serviceNames = $order->orderItems->map(function ($item) {
                    return [
                        'name' => $item->service->name,
                        'qty' => $item->qty,
                        'price' => $item->price,
                        'total' => $item->total
                    ];
                });
                
                return [
                    'order_number' => $order->order_number,
                    'date' => $order->order_date->format('Y-m-d'),
                    'customer' => $order->customer ? $order->customer->name : 'Walk-in Customer',
                    'customer_id' => $order->customer_id,
                    'services' => $serviceNames,
                    'amount' => $order->total,
                    'status' => $order->status,
                    'status_display' => ucfirst($order->status),
                    'paid_amount' => $order->paid_amount,
                    'outstanding' => $order->total - $order->paid_amount,
                    'notes' => $order->notes
                ];
            });
        
        // Get summary
        $summary = [
            'total_orders' => $orders->count(),
            'total_amount' => $orders->sum('amount'),
            'completed_count' => $orders->where('status', 'completed')->count(),
            'pending_count' => $orders->where('status', 'pending')->count(),
            'processing_count' => $orders->where('status', 'processing')->count(),
            'cancelled_count' => $orders->where('status', 'cancelled')->count()
        ];
        
        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders,
                'summary' => $summary,
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $status
                ]
            ],
            'message' => 'Order report data retrieved successfully'
        ]);
    }
}