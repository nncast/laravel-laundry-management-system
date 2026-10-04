<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $orders = Order::with(['customer:id,name', 'staff:id,name'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('staff', fn ($s) => $s->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('orders', compact('orders', 'search'));
    }

    /**
     * There is no separate "show" page – the details page is the order view.
     */
    public function show(Order $order)
    {
        return redirect()->route('orders.details', $order);
    }

    /**
     * Printing is done from the details page (it has print styles).
     */
    public function print(Order $order)
    {
        return redirect()->route('orders.details', ['order' => $order, 'print' => 1]);
    }

    /**
     * Order details page.
     */
    public function details(Order $order)
    {
        $order->load([
            'customer',
            'staff:id,name',
            'items.service:id,name',
            'addons',
            'payments',
        ]);

        $settings = SystemSetting::first() ?? new SystemSetting();

        return view('order-details', compact('order', 'settings'));
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Order::STATUSES),
        ]);

        $order->update(['status' => $request->status]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully.',
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                ],
            ]);
        }

        return back()->with('success', 'Order status updated successfully.');
    }

    /**
     * Add payment to order.
     */
    public function addPayment(Request $request, Order $order)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:99999999',
            'payment_method' => 'nullable|string|in:cash,card,transfer,other',
        ]);

        if ($order->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot add a payment to a cancelled order.',
            ], 422);
        }

        $balance = $order->balance;
        if ($balance <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'This order is already fully paid.',
            ], 422);
        }

        // Record only what is owed; anything above that is change for the customer
        $amount = min((float) $validated['amount'], $balance);
        $change = round((float) $validated['amount'] - $amount, 2);

        DB::transaction(function () use ($order, $amount, $validated) {
            $order->addPayment($amount, $validated['payment_method'] ?? 'cash');
        });

        $order->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Payment added successfully.',
            'change' => $change,
            'order' => [
                'id' => $order->id,
                'paid_amount' => (float) $order->paid_amount,
                'balance' => $order->balance,
            ],
        ]);
    }

    /**
     * Replace the order notes.
     */
    public function addNotes(Request $request, Order $order)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $order->update(['notes' => $validated['notes'] ?? null]);

        return response()->json([
            'success' => true,
            'message' => 'Notes saved successfully.',
            'notes' => $order->notes,
        ]);
    }

    /**
     * Remove the specified order.
     */
    public function destroy(Order $order)
    {
        if ($order->payments()->exists()) {
            return back()->with('error', 'Cannot delete an order that has payments. Cancel it instead.');
        }

        $order->delete();

        return redirect()->route('orders.index')->with('success', 'Order deleted successfully.');
    }
}
