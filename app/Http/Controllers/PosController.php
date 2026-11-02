<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{
    Service,
    Customer,
    Addon,
    Order,
    OrderItem,
    Staff
};
use App\Support\Format;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function index(Request $request)
    {
        return view('pos', $this->formData() + [
            'last_order_number' => (Order::max('id') ?? 0) + 1,
            // "New order" on the customers page opens the POS with that customer picked
            'preselectedCustomerId' => Customer::whereKey($request->integer('customer'))->value('id'),
        ]);
    }

    /**
     * What the POS shows once a customer is picked: how often they come, what
     * they still owe, and their last order so it can be repeated in one tap.
     */
    public function customerSummary(Customer $customer)
    {
        $orders = Order::where('customer_id', $customer->id);
        $last = (clone $orders)->with(['items.service:id,name,is_active', 'addons:id,name,is_active'])
            ->where('status', '!=', 'cancelled')
            ->latest('order_date')->latest('id')
            ->first();

        return response()->json([
            'success' => true,
            'orders_count' => (clone $orders)->count(),
            'balance_due' => round((float) (clone $orders)->unpaid()->inBusinessCurrency()->sum(DB::raw('total - paid_amount')), 2),
            'last_order' => $last ? [
                'order_number' => $last->order_number,
                'order_date' => fmt_date($last->order_date),
                'items' => $last->items->map(fn ($item) => [
                    'service_id' => $item->service_id,
                    'name' => $item->service->name ?? 'Deleted service',
                    'qty' => $item->qty,
                    'available' => (bool) ($item->service->is_active ?? false),
                ])->values(),
                'addon_ids' => $last->addons->where('is_active', true)->pluck('id')->values(),
            ] : null,
        ]);
    }

    /**
     * Show the form for editing an existing order.
     */
    public function edit(Order $order)
    {
        if (!$order->can_edit) {
            return redirect()->route('orders.details', $order)
                ->with('error', $this->notEditableReason($order));
        }

        $order->load([
            'customer',
            'items.service:id,name',
            'addons',
            'payments',
        ]);

        return view('pos', $this->formData() + [
            'order' => $order,
            'last_order_number' => $order->id,
        ]);
    }

    /**
     * Update an existing order.
     */
    public function update(Request $request, Order $order)
    {
        if (!$order->can_edit) {
            return response()->json([
                'success' => false,
                'message' => $this->notEditableReason($order),
            ], 403);
        }

        $validated = $this->validateOrder($request);

        try {
            $order = DB::transaction(fn () => $this->saveOrder($order, $validated));
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return $this->failure('Order update failed', $e, 'Failed to update order. Please try again.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully!',
            'order' => $this->orderSummary($order),
        ]);
    }

    /**
     * Create a new order
     */
    public function createOrder(Request $request)
    {
        $staff = Staff::find(Session::get('staff.id'));
        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required. Please login again.',
            ], 401);
        }

        $validated = $this->validateOrder($request);

        try {
            $order = DB::transaction(function () use ($staff, $validated) {
                $order = new Order([
                    'staff_id' => $staff->id,
                    'status' => 'pending',
                ]);

                return $this->saveOrder($order, $validated);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return $this->failure('Order creation failed', $e, 'Failed to create order. Please try again.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully!',
            'order' => $this->orderSummary($order),
            'order_number' => $order->order_number,
        ]);
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
            'addons' => $addons,
        ]);
    }

    /**
     * Check if staff is authenticated (API endpoint)
     */
    public function checkAuth()
    {
        return response()->json([
            'authenticated' => true,
            'staff_id' => Session::get('staff.id'),
            'staff_name' => Session::get('staff.name'),
            'staff_role' => Session::get('staff.role'),
        ]);
    }

    // ------------------------------------------------------------------

    private function formData(): array
    {
        $services = Service::with('serviceType:id,name')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'icon', 'service_type_id']);

        // POS sections: one per service type (A-Z), services without a type last
        $serviceGroups = $services
            ->groupBy(fn ($service) => $service->serviceType->name ?? 'Other')
            ->sortKeys(SORT_NATURAL | SORT_FLAG_CASE);
        if ($serviceGroups->has('Other')) {
            $serviceGroups = $serviceGroups->except('Other')->put('Other', $serviceGroups->get('Other'));
        }

        // Customers of the latest orders, newest first: one tap for regulars
        $recentCustomerIds = Order::whereNotNull('customer_id')->latest('id')->take(40)
            ->pluck('customer_id')->unique()->take(6)->values();

        return [
            'services' => $services,
            'serviceGroups' => $serviceGroups,
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'contact']),
            'recentCustomerIds' => $recentCustomerIds,
            'addons' => Addon::where('is_active', 1)->orderBy('name')->get(['id', 'name', 'price']),
        ];
    }

    private function validateOrder(Request $request): array
    {
        return $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'discount' => ['nullable', 'numeric', 'min:0', Rule::when($request->input('discount_type') === 'percent', 'max:100')],
            'discount_type' => 'nullable|in:amount,percent',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|integer|exists:services,id',
            'items.*.qty' => 'required|integer|min:1|max:10000',
            'addons' => 'nullable|array',
            'addons.*.addon_id' => 'required|integer|exists:addons,id',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,card,transfer,other',
        ], [
            'customer_id.required' => 'Please select a customer.',
            'items.required' => 'Please add at least one service.',
            'items.min' => 'Please add at least one service.',
            'discount.max' => 'A percentage discount can be at most 100%.',
        ]);
    }

    /**
     * Persist the order header, items, add-ons and (optional) new payment.
     *
     * Prices come from the database, not the browser: items/add-ons already on
     * the order keep the price they were sold at, new ones use the current price.
     */
    private function saveOrder(Order $order, array $data): Order
    {
        $isNew = !$order->exists;

        // ---- Header ----
        // A percentage is kept as entered; calculateTotals() turns it into pesos below.
        $discount = round((float) ($data['discount'] ?? 0), 2);
        $percent = ($data['discount_type'] ?? 'amount') === 'percent' && $discount > 0;
        $order->fill([
            'customer_id' => $data['customer_id'],
            'order_date' => $data['order_date'],
            'notes' => $data['notes'] ?? null,
            'discount' => $percent ? 0 : $discount,
            'discount_percent' => $percent ? $discount : null,
        ]);
        $order->save();

        // ---- Items (merge duplicate services into one line) ----
        $quantities = [];
        foreach ($data['items'] as $item) {
            $serviceId = (int) $item['service_id'];
            $quantities[$serviceId] = ($quantities[$serviceId] ?? 0) + (int) $item['qty'];
        }

        $existingItems = $isNew ? collect() : $order->items()->get()->keyBy('service_id');
        $newServiceIds = array_diff(array_keys($quantities), $existingItems->keys()->all());
        $services = Service::whereIn('id', $newServiceIds)->get(['id', 'price', 'is_active'])->keyBy('id');

        foreach ($quantities as $serviceId => $qty) {
            if ($line = $existingItems->get($serviceId)) {
                if ($line->qty !== $qty) {
                    $line->qty = $qty;
                    $line->save();
                }
                continue;
            }

            $service = $services->get($serviceId);
            if (!$service || !$service->is_active) {
                throw ValidationException::withMessages([
                    'items' => 'One of the selected services is no longer available. Please refresh the page.',
                ]);
            }

            OrderItem::create([
                'order_id' => $order->id,
                'service_id' => $serviceId,
                'price' => $service->price,
                'rate' => 1,
                'qty' => $qty,
            ]);
        }

        if (!$isNew) {
            $order->items()->whereNotIn('service_id', array_keys($quantities))->delete();
        }

        // ---- Add-ons ----
        $addonIds = array_values(array_unique(array_map(
            fn ($a) => (int) $a['addon_id'],
            $data['addons'] ?? []
        )));

        $existingAddonPrices = $isNew
            ? collect()
            : $order->addons()->get()->pluck('pivot.price', 'id');

        $newAddons = Addon::whereIn('id', array_diff($addonIds, $existingAddonPrices->keys()->all()))
            ->get(['id', 'price', 'is_active'])
            ->keyBy('id');

        $sync = [];
        foreach ($addonIds as $addonId) {
            if ($existingAddonPrices->has($addonId)) {
                $sync[$addonId] = ['price' => $existingAddonPrices[$addonId]];
                continue;
            }

            $addon = $newAddons->get($addonId);
            if (!$addon || !$addon->is_active) {
                throw ValidationException::withMessages([
                    'addons' => 'One of the selected add-ons is no longer available. Please refresh the page.',
                ]);
            }
            $sync[$addonId] = ['price' => $addon->price];
        }
        $order->addons()->sync($sync);

        // ---- Totals ----
        $order->calculateTotals();

        // ---- Payment (amount tendered; only what is owed is recorded) ----
        $order->syncPaidAmount();
        $tendered = round((float) ($data['payment_amount'] ?? 0), 2);
        $owed = max(0, round((float) $order->total - (float) $order->paid_amount, 2));
        $applied = min($tendered, $owed);

        if ($applied > 0) {
            $order->payments()->create([
                'amount' => $applied,
                'payment_method' => $data['payment_method'] ?? 'cash',
            ]);
            $order->syncPaidAmount();
        }

        // A fully paid pending order moves to processing; never demote an order
        if ($order->status === 'pending' && $order->total > 0 && $order->balance <= 0) {
            $order->status = 'processing';
        }

        $order->save();

        return $order->fresh();
    }

    private function notEditableReason(Order $order): string
    {
        return $order->currency !== app(Format::class)->currency
            ? "This order was taken in {$order->currency}. Switch the business currency back to {$order->currency} to edit it."
            : 'This order cannot be edited because it is already ' . $order->status . '.';
    }

    private function orderSummary(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'total' => (float) $order->total,
            'paid_amount' => (float) $order->paid_amount,
            'balance' => $order->balance,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'currency' => $order->currency,
        ];
    }

    private function failure(string $logMessage, \Throwable $e, string $userMessage)
    {
        Log::error($logMessage, [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'success' => false,
            'message' => $userMessage,
            'debug' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
}
