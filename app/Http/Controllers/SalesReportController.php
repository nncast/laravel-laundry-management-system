<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderAddon;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters from request or use defaults
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        
        // Validate dates
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = date('Y-m-01');
        }
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = date('Y-m-d');
        }
        
        // Ensure end date is not before start date
        if ($startDate > $endDate) {
            $endDate = $startDate;
        }
        
        // Get sales data with filters
        $salesData = $this->getSalesData($startDate, $endDate);
        
        // Get summary statistics
        $summary = $this->getSummaryStats($startDate, $endDate);
        
        return view('report-sales', [
            'salesData' => $salesData,
            'summary' => $summary,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }
    
    private function getSalesData($startDate, $endDate)
    {
        // Get orders with their items and addons
        return Order::with(['customer', 'items.service', 'addons'])
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled') // Exclude cancelled orders
            ->orderBy('order_date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($order) {
                // Calculate addon total
                $addonTotal = $order->addons->sum('pivot.price');
                
                // Calculate items total from order items
                $itemsTotal = $order->items->sum('total');
                
                return [
                    'order' => $order,
                    'date' => $order->order_date->format('Y-m-d'),
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer ? $order->customer->name : 'Walk-in Customer',
                    'customer' => $order->customer,
                    'items_total' => $itemsTotal,
                    'addon_total' => $addonTotal,
                    'subtotal' => $order->subtotal, // This should be items_total + addon_total
                    'discount' => $order->discount,
                    'total' => $order->total,
                    'paid_amount' => $order->paid_amount,
                    'outstanding' => $order->total - $order->paid_amount,
                    'status' => $order->status,
                    'items' => $order->items,
                    'addons' => $order->addons
                ];
            });
    }
    
    private function getSummaryStats($startDate, $endDate)
    {
        // Get orders for the period
        $orders = Order::whereBetween('order_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->get();
        
        // Get total items sold count
        $totalItemsSold = OrderItem::whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('order_date', [$startDate, $endDate])
                      ->where('status', '!=', 'cancelled');
            })
            ->sum('qty');
        
        // Get total addons count
        $totalAddonsSold = OrderAddon::whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('order_date', [$startDate, $endDate])
                      ->where('status', '!=', 'cancelled');
            })
            ->count();
        
        // Calculate average order value
        $averageOrderValue = $orders->count() > 0 ? $orders->avg('total') : 0;
        
        // Get completed orders
        $completedOrders = $orders->where('status', 'completed')->count();
        
        return [
            'total_orders' => $orders->count(),
            'total_sales' => $orders->sum('total'),
            'total_discount' => $orders->sum('discount'),
            'total_paid' => $orders->sum('paid_amount'),
            'total_outstanding' => $orders->sum('total') - $orders->sum('paid_amount'),
            'total_items_sold' => $totalItemsSold,
            'total_addons_sold' => $totalAddonsSold,
            'average_order_value' => $averageOrderValue,
            'completed_orders' => $completedOrders,
            'completion_rate' => $orders->count() > 0 ? ($completedOrders / $orders->count()) * 100 : 0
        ];
    }
    
    public function download(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        
        // Get sales data for download
        $salesData = $this->getSalesData($startDate, $endDate);
        $summary = $this->getSummaryStats($startDate, $endDate);
        
        // Generate CSV content
        $csvContent = "Sales Report - {$startDate} to {$endDate}\n\n";
        $csvContent .= "Date,Order#,Customer,Subtotal,Addon Total,Discount,Total,Paid Amount,Outstanding,Status\n";
        
        foreach ($salesData as $sale) {
            $csvContent .= sprintf(
                '"%s","%s","%s",%.2f,%.2f,%.2f,%.2f,%.2f,%.2f,"%s"',
                $sale['date'],
                $sale['order_number'],
                $sale['customer_name'],
                $sale['subtotal'],
                $sale['addon_total'],
                $sale['discount'],
                $sale['total'],
                $sale['paid_amount'],
                $sale['outstanding'],
                ucfirst($sale['status'])
            ) . "\n";
        }
        
        // Summary section
        $csvContent .= "\n\nSummary\n";
        $csvContent .= "Total Orders:," . $summary['total_orders'] . "\n";
        $csvContent .= "Total Sales:," . $summary['total_sales'] . "\n";
        $csvContent .= "Total Discount:," . $summary['total_discount'] . "\n";
        $csvContent .= "Total Paid:," . $summary['total_paid'] . "\n";
        $csvContent .= "Total Outstanding:," . $summary['total_outstanding'] . "\n";
        $csvContent .= "Total Items Sold:," . $summary['total_items_sold'] . "\n";
        $csvContent .= "Total Addons Sold:," . $summary['total_addons_sold'] . "\n";
        $csvContent .= "Average Order Value:," . number_format($summary['average_order_value'], 2) . "\n";
        $csvContent .= "Completed Orders:," . $summary['completed_orders'] . "\n";
        $csvContent .= "Completion Rate:," . number_format($summary['completion_rate'], 2) . "%\n";
        $csvContent .= "\nGenerated on: " . now()->format('Y-m-d H:i:s');
        
        $filename = "sales_report_{$startDate}_to_{$endDate}.csv";
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    
    public function apiData(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        
        // Get sales data
        $salesData = $this->getSalesData($startDate, $endDate);
        $summary = $this->getSummaryStats($startDate, $endDate);
        
        return response()->json([
            'success' => true,
            'data' => [
                'sales' => $salesData,
                'summary' => $summary,
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ],
            'message' => 'Sales report data retrieved successfully'
        ]);
    }
}