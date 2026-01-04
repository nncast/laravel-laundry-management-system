<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{
    Service,
    Customer,
    Addon,
    Order,
    OrderItem,
    Payment,
    Staff
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PosController extends Controller
{
    public function index()
    {
        $services = Service::where('is_active', 1)
            ->orderBy('name', 'asc')
            ->get();

        $customers = Customer::orderBy('name', 'asc')->get();
        
        $addons = Addon::where('is_active', 1)
            ->orderBy('name', 'asc')
            ->get();
        
        // Get the last order ID and increment by 1
        $lastOrder = Order::latest('id')->first();
        $last_order_number = $lastOrder ? ($lastOrder->id + 1) : 1;

        return view('pos', compact('services', 'customers', 'addons', 'last_order_number'));
    }

    /**
     * Show the form for editing an existing order.
     */
    public function edit(Order $order)
{
    // Check if order can be edited
    if (!$order->can_edit) {
        return redirect()->route('orders.details', $order)
            ->with('error', 'This order cannot be edited because it is already ' . $order->status . '.');
    }

    // Load the order with relationships
    $order->load([
        'customer',
        'staff',
        'items.service',
        'addons',
        'payments'
    ]);

    $services = Service::where('is_active', 1)
        ->orderBy('name', 'asc')
        ->get();

    $customers = Customer::orderBy('name', 'asc')->get();
    
    $addons = Addon::where('is_active', 1)
        ->orderBy('name', 'asc')
        ->get();
    
    // Use existing order number
    $last_order_number = $order->id; // Use the actual order ID

    return view('pos', compact('order', 'services', 'customers', 'addons', 'last_order_number'));
}
    /**
     * Update an existing order.
     */
    public function update(Request $request, Order $order)
{
    // Check if order can be edited
    if (!$order->can_edit) {
        return response()->json([
            'success' => false,
            'message' => 'This order cannot be edited because it is already ' . $order->status . '.'
        ], 403);
    }

    // Validate request
    $validated = $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'order_date' => 'required|date',
        'notes' => 'nullable|string|max:500',
        'discount' => 'required|numeric|min:0',
        'items' => 'required|array|min:1',
        'items.*.service_id' => 'required|exists:services,id',
        'items.*.qty' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
        'items.*.id' => 'nullable|exists:order_items,id', // For existing items
        'addons' => 'nullable|array',
        'addons.*.addon_id' => 'required|exists:addons,id',
        'addons.*.price' => 'required|numeric|min:0',
        'payment_amount' => 'nullable|numeric|min:0',
        'payment_method' => 'nullable|in:cash,card,transfer,other'
    ]);

    // Start database transaction
    DB::beginTransaction();

    try {
        // Calculate totals
        $subtotal = collect($validated['items'])->sum(function($item) {
            return $item['price'] * $item['qty'];
        });

        $addonsTotal = collect($validated['addons'] ?? [])->sum('price');
        $discount = $validated['discount'];
        $total = ($subtotal + $addonsTotal) - $discount;
        $total = max(0, $total);

        // Update order details
        $order->update([
            'customer_id' => $validated['customer_id'],
            'order_date' => $validated['order_date'],
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'paid_amount' => $validated['payment_amount'] ?? 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update order status based on payment
        if ($validated['payment_amount'] && $validated['payment_amount'] >= $total && $total > 0) {
            $order->status = 'processing';
        } else {
            $order->status = 'pending';
        }
        $order->save();

        // Handle order items
        $existingItemIds = [];
        foreach ($validated['items'] as $item) {
            if (isset($item['id'])) {
                // Update existing item
                $orderItem = OrderItem::where('order_id', $order->id)
                    ->where('id', $item['id'])
                    ->first();
                
                if ($orderItem) {
                    $orderItem->update([
                        'service_id' => $item['service_id'],
                        'price' => $item['price'],
                        'qty' => $item['qty'],
                        'total' => $item['price'] * $item['qty'],
                    ]);
                    $existingItemIds[] = $orderItem->id;
                }
            } else {
                // Create new item
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'service_id' => $item['service_id'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'total' => $item['price'] * $item['qty'],
                ]);
                $existingItemIds[] = $orderItem->id;
            }
        }

        // Delete items that were removed
        OrderItem::where('order_id', $order->id)
            ->whereNotIn('id', $existingItemIds)
            ->delete();

        // Handle addons
        $order->addons()->detach(); // Remove all existing addons
        if (!empty($validated['addons'])) {
            $addonData = [];
            foreach ($validated['addons'] as $addon) {
                $addonData[$addon['addon_id']] = ['price' => $addon['price']];
            }
            $order->addons()->attach($addonData);
        }

        // Handle payments
        $paymentAmount = $validated['payment_amount'] ?? 0;
        if ($paymentAmount > 0) {
            // Delete existing payments and create new one
            $order->payments()->delete();
            
            Payment::create([
                'order_id' => $order->id,
                'amount' => $paymentAmount,
                'payment_method' => $validated['payment_method'] ?? 'cash',
            ]);
        }

        // Commit transaction
        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully!',
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total,
                'balance' => $order->balance,
                'status' => $order->status,
                'status_label' => $order->status_label,
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        
        \Log::error('Order update failed', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to update order. Please try again.',
            'debug' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

    /**
     * Get active addons for POS (API)
     */
    public function getActiveAddons()
    {
        $addons = Addon::where('is_active', 1)
            ->select('id', 'name', 'price')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'addons' => $addons
        ]);
    }

    /**
     * Create a new order
     */
    public function createOrder(Request $request)
    {
        // Check if staff is logged in
        if (!Session::has('staff.id')) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required. Please login again.',
            ], 401);
        }
        
        // Get staff ID from session
        $staffId = Session::get('staff.id');

        // Validate request
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'discount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'addons' => 'nullable|array',
            'addons.*.addon_id' => 'required|exists:addons,id',
            'addons.*.price' => 'required|numeric|min:0',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,card,transfer,other'
        ]);

        // Start database transaction
        DB::beginTransaction();

        try {
            // Calculate totals
            $subtotal = collect($validated['items'])->sum(function($item) {
                return $item['price'] * $item['qty'];
            });

            $addonsTotal = collect($validated['addons'] ?? [])->sum('price');
            $discount = $validated['discount'];
            $total = ($subtotal + $addonsTotal) - $discount;
            $total = max(0, $total); // Ensure total is not negative

            // Determine order status - only use allowed statuses
            $paymentAmount = $validated['payment_amount'] ?? 0;
            $status = 'pending';
            if ($paymentAmount >= $total && $total > 0) {
                $status = 'processing';
            }

            // Get staff object to ensure it exists
            $staff = Staff::findOrFail($staffId);

            // Create the order (order_number will be auto-generated by the model)
            $order = Order::create([
                'customer_id' => $validated['customer_id'],
                'staff_id' => $staffId,
                'order_date' => $validated['order_date'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'paid_amount' => $paymentAmount,
                'status' => $status,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create order items
            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'service_id' => $item['service_id'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'total' => $item['price'] * $item['qty'],
                ]);
            }

            // Create order addons if any (using the relationship)
            if (!empty($validated['addons'])) {
                $addonData = [];
                foreach ($validated['addons'] as $addon) {
                    $addonData[$addon['addon_id']] = ['price' => $addon['price']];
                }
                $order->addons()->attach($addonData);
            }

            // Create payment record if payment was made
            if ($paymentAmount > 0) {
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $paymentAmount,
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                ]);
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully!',
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                    'balance' => $order->balance,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                ],
                'order_number' => $order->order_number,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Check if staff is authenticated (API endpoint)
     */
    public function checkAuth()
    {
        if (Session::has('staff.id')) {
            return response()->json([
                'authenticated' => true,
                'staff_id' => Session::get('staff.id'),
                'staff_name' => Session::get('staff.name'),
                'staff_role' => Session::get('staff.role'),
            ]);
        }
        
        return response()->json([
            'authenticated' => false
        ], 401);
    }
}