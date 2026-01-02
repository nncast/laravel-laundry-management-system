<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $orders = Order::with(['customer', 'staff', 'payments'])
            ->when($search, function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('staff', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            })
            ->latest()
            ->paginate(20);

        // FLAT VIEW
        return view('orders', compact('orders', 'search'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load([
            'customer',
            'staff',
            'orderItems.service',
            'orderAddons.addon',
            'payments'
        ]);

        // FLAT VIEW
        return view('order-show', compact('order'));
    }

    /**
     * Print order invoice.
     */
    public function print(Order $order)
    {
        $order->load([
            'customer',
            'staff',
            'orderItems.service',
            'orderAddons.addon',
            'payments'
        ]);

        // FLAT VIEW
        return view('order-print', compact('order'));
    }

    /**
     * Update order status.
     */
     public function updateStatus(Request $request, Order $order)
    {
        // Validate the status - make sure it matches your ENUM values
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled' // Remove 'ready' if not in your ENUM
        ]);

        // Update the order status
        $order->update([
            'status' => $request->status
        ]);

        // For AJAX requests, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully.',
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'status_label' => ucfirst($order->status)
                ]
            ]);
        }

        // For regular form submissions, redirect back
        return back()->with('success', 'Order status updated successfully.');
    }

    /**
     * Add payment to order.
     */
     public function addPayment(Request $request, Order $order)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50'
        ]);

        $order->payments()->create([
            'amount' => $request->amount,
            'payment_method' => $request->payment_method
        ]);

        $order->update([
            'paid_amount' => $order->paid_amount + $request->amount
        ]);

        return back()->with('success', 'Payment added successfully.');
    }
    /**
     * Remove the specified order.
     */
    public function destroy(Order $order)
    {
        if ($order->payments()->exists()) {
            return back()->with('error', 'Cannot delete order with payment records.');
        }

        $order->delete();

        return back()->with('success', 'Order deleted successfully.');
    }

    // Add this method to your OrderController.php
public function details(Order $order)
{
    // Load relationships
    $order->load([
        'customer',
        'staff',
        'items.service',
        'addons',
        'payments'
    ]);
    $settings = \App\Models\SystemSetting::first() ?? new \App\Models\SystemSetting();
    
    // If your file is named order-details.blade.php in views folder
    return view('order-details', [
        'order' => $order,
        'settings' => $settings
    ]);
    
    // OR if it's in resources/views/orders/order-details.blade.php
    // return view('orders.order-details', [
    //     'order' => $order,
    //     'settings' => $settings
    // ]);
}
}
