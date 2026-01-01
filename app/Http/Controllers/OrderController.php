<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\OrderAddon;
use App\Models\Service;
use App\Models\Addon;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'order_date' => 'required|date',
            'subtotal' => 'required|numeric|min:0',
            'addon_total' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
            'net_total' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:services,id',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.total' => 'required|numeric|min:0',
            'addons' => 'nullable|array',
            'addons.*.id' => 'required|exists:addons,id',
            'addons.*.price' => 'required|numeric|min:0',
            'payment' => 'nullable|array',
            'payment.amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Generate unique order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . Str::random(6);
            
            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $validated['customer_id'] ?? null,
                'user_id' => auth()->id(), // Assuming user is logged in
                'order_type' => 'pickup', // Default to pickup, you can change this later
                'order_date' => $validated['order_date'],
                'subtotal' => $validated['subtotal'],
                'discount' => $validated['discount'],
                'total' => $validated['net_total'],
                'paid_amount' => $validated['payment']['amount'] ?? 0,
                'status' => $validated['payment']['amount'] ? 'processing' : 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Save order items
            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'service_id' => $item['id'],
                    'price' => $item['price'],
                    'rate' => 1, // Default rate, you can adjust if needed
                    'qty' => $item['qty'],
                    'total' => $item['total'],
                ]);
            }

            // Save addons if any
            if (!empty($validated['addons'])) {
                foreach ($validated['addons'] as $addon) {
                    OrderAddon::create([
                        'order_id' => $order->id,
                        'addon_id' => $addon['id'],
                        'price' => $addon['price'],
                    ]);
                }
            }

            // Save payment if exists
            if (!empty($validated['payment']) && $validated['payment']['amount'] > 0) {
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $validated['payment']['amount'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order saved successfully!',
                'order' => $order->load(['items', 'addons', 'payment']),
                'order_number' => $orderNumber,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save order: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Additional method to get all orders (for listing)
    public function index()
    {
        $orders = Order::with(['customer', 'items.service', 'addons.addon', 'payment'])
                      ->orderBy('created_at', 'desc')
                      ->paginate(20);
        
        return response()->json($orders);
    }

    // Method to get a single order
    public function show($id)
    {
        $order = Order::with(['customer', 'items.service', 'addons.addon', 'payment'])
                     ->findOrFail($id);
        
        return response()->json($order);
    }
}