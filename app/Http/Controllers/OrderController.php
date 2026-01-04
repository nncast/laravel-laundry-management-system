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
     * Add notes to order.
     */
    public function addNotes(Request $request, Order $order)
    {
        $request->validate([
            'notes' => 'required|string|max:1000'
        ]);

        $order->update([
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notes updated successfully.'
        ]);
    }

/**
 * Add payment to order.
 */
/**
 * Add payment to order.
 */
public function addPayment(Request $request, Order $order)
{
    \Log::info('===== ADD PAYMENT START =====');
    \Log::info('Order ID:', ['id' => $order->id]);
    \Log::info('Request data:', $request->all());
    \Log::info('Is AJAX:', ['ajax' => $request->ajax()]);
    \Log::info('Paid amount before:', ['paid_amount' => $order->paid_amount]);

    try {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        \Log::info('Validation passed');

        // Create payment
        $payment = $order->payments()->create([
            'amount' => $request->amount
        ]);

        \Log::info('Payment created:', ['payment_id' => $payment->id, 'amount' => $payment->amount]);

        // Update order's paid amount
        $newPaidAmount = $order->paid_amount + $request->amount;
        $order->update([
            'paid_amount' => $newPaidAmount
        ]);

        \Log::info('Order updated:', [
            'new_paid_amount' => $newPaidAmount,
            'order_id' => $order->id
        ]);

        \Log::info('===== ADD PAYMENT SUCCESS =====');

        return response()->json([
            'success' => true,
            'message' => 'Payment added successfully.',
            'payment' => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'created_at' => $payment->created_at
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Payment error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'order_id' => $order->id
        ]);

        \Log::info('===== ADD PAYMENT FAILED =====');

        return response()->json([
            'success' => false,
            'message' => 'Error adding payment: ' . $e->getMessage()
        ], 500);
    }
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

    /**
     * Order details view.
     */
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
        
        // order-details.blade.php in views folder
        return view('order-details', [
            'order' => $order,
            'settings' => $settings
        ]);
    }
}