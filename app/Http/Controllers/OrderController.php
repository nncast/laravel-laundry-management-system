<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderReportController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters from request or use defaults
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
        
        // Ensure end date is not before start date
        if ($startDate > $endDate) {
            $endDate = $startDate;
        }
        
        // Get orders with filters
        $orders = $this->getFilteredOrders($startDate, $endDate, $status);
        
        // Get summary statistics
        $summary = $this->getSummaryStats($startDate, $endDate, $status);
        
        return view('report-orders', [
            'orders' => $orders,
            'summary' => $summary,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status
            ]
        ]);
    }
    
    private function getFilteredOrders($startDate, $endDate, $status = '')
{
    $query = Order::with(['customer', 'items.service']) // Changed from 'orderItems.service' to 'items.service'
        ->whereBetween('order_date', [$startDate, $endDate])
        ->orderBy('order_date', 'desc')
        ->orderBy('id', 'desc');
    
    // Apply status filter if provided
    if (!empty($status)) {
        $query->where('status', $status);
    }
    
    return $query->get();
}
    
    private function getSummaryStats($startDate, $endDate, $status = '')
    {
        $query = Order::whereBetween('order_date', [$startDate, $endDate]);
        
        // Apply status filter if provided
        if (!empty($status)) {
            $query->where('status', $status);
        }
        
        $orders = $query->get();
        
        return [
            'total_orders' => $orders->count(),
            'total_amount' => $orders->sum('total'),
            'completed_count' => $orders->where('status', 'completed')->count(),
            'pending_count' => $orders->where('status', 'pending')->count(),
            'processing_count' => $orders->where('status', 'processing')->count(),
            'cancelled_count' => $orders->where('status', 'cancelled')->count(),
        ];
    }
    
    public function download(Request $request)
{
    $startDate = $request->input('start_date', date('Y-m-01'));
    $endDate = $request->input('end_date', date('Y-m-t'));
    $status = $request->input('status', '');
    
    // Get orders without pagination for download
    $orders = Order::with(['customer', 'items.service']) // Changed from 'orderItems.service' to 'items.service'
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
        $serviceNames = $order->items->map(function ($item) { // Changed from $order->orderItems to $order->items
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
        $summary = $this->getSummaryStats($startDate, $endDate, $status);
        $csvContent .= "\n\nSummary\n";
        $csvContent .= "Total Orders:," . $summary['total_orders'] . "\n";
        $csvContent .= "Total Amount:," . $summary['total_amount'] . "\n";
        $csvContent .= "Completed:," . $summary['completed_count'] . "\n";
        $csvContent .= "Pending:," . $summary['pending_count'] . "\n";
        $csvContent .= "Processing:," . $summary['processing_count'] . "\n";
        $csvContent .= "Cancelled:," . $summary['cancelled_count'] . "\n";
        $csvContent .= "\nGenerated on: " . now()->format('Y-m-d H:i:s');
        
        $filename = "order_report_{$startDate}_to_{$endDate}.csv";
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}