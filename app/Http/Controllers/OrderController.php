<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Staff;
use App\Models\SystemSetting;
use Carbon\Carbon;
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
        $from = $this->dateInput($request, 'from', '');
        $to = $this->dateInput($request, 'to', '');
        $payment = in_array($request->input('payment'), ['unpaid', 'partial', 'paid'], true) ? $request->input('payment') : '';
        // A status, "open" (still in the shop) or "overdue"
        $status = (string) $request->input('status', '');
        if (!in_array($status, [...Order::STATUSES, 'open', 'overdue'], true)) {
            $status = '';
        }

        // Everything except the status tab, so each tab can show how many orders it holds
        $filtered = Order::query()
            ->when($search !== '', fn ($q) => $this->search($q, $search))
            ->when($payment === 'unpaid', fn ($q) => $q->unpaid())
            ->when($payment === 'partial', fn ($q) => $q->unpaid()->where('paid_amount', '>', 0))
            ->when($payment === 'paid', fn ($q) => $q->where('total', '>', 0)->whereColumn('paid_amount', '>=', 'total'))
            ->when($from !== '', fn ($q) => $q->where('order_date', '>=', $from))
            ->when($to !== '', fn ($q) => $q->where('order_date', '<', Carbon::parse($to)->addDay()->toDateString()));

        $byStatus = (clone $filtered)->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');
        $counts = ['' => (int) $byStatus->sum(), 'open' => (int) $byStatus->only(Order::OPEN_STATUSES)->sum(), 'overdue' => (clone $filtered)->overdue()->count()];
        foreach (Order::STATUSES as $name) {
            $counts[$name] = (int) ($byStatus[$name] ?? 0);
        }

        $orders = $filtered
            ->with(['customer:id,name,contact', 'staff:id,name'])
            ->when(in_array($status, Order::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->when($status === 'open', fn ($q) => $q->whereIn('status', Order::OPEN_STATUSES))
            ->when($status === 'overdue', fn ($q) => $q->overdue())
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('orders', compact('orders', 'search', 'status', 'payment', 'from', 'to', 'counts'));
    }

    /**
     * Orders matching a search: the order number, or the customer's name or phone,
     * or the staff member who took it. Customers and staff are looked up once
     * (not per order), and an order-number-looking term matches from the start so
     * the unique index on order_number is used.
     */
    private function search($query, string $term)
    {
        // An order number (ORD-1A2B…) matches from the start, using the unique index
        if (preg_match('/^[A-Za-z]{2,5}-[0-9A-Za-z]+$/', $term)) {
            return $query->where('order_number', 'like', "{$term}%");
        }

        $digits = preg_replace('/\D/', '', $term);
        $customers = Customer::select('id')->where(fn ($c) => $c->where('name', 'like', "%{$term}%")
            ->when(strlen($digits) >= 3, fn ($c) => $c->orWhere('contact', 'like', "{$digits}%")));
        $staffIds = Staff::where('name', 'like', "%{$term}%")->pluck('id');

        // Kept as simple as the term allows: the customer subquery on its own is joined
        // efficiently; every extra OR makes the database check orders one by one
        return $query->where(function ($q) use ($term, $digits, $customers, $staffIds) {
            $q->whereIn('customer_id', $customers);
            if ($staffIds->isNotEmpty()) {
                $q->orWhereIn('staff_id', $staffIds);
            }
            // Order numbers always contain digits, so a plain name never scans them
            if ($digits !== '') {
                $q->orWhere('order_number', 'like', "%{$term}%");
            }
        });
    }

    /**
     * Type-ahead for the order search in the top bar (and barcode scanners):
     * an exact order number comes back as "exact" so the page can open it at once.
     */
    public function lookup(Request $request)
    {
        $term = trim((string) $request->input('q'));
        if (mb_strlen($term) < 2) {
            return response()->json(['exact' => null, 'orders' => []]);
        }

        $exact = Order::where('order_number', $term)->value('id');
        $orders = $this->search(Order::query(), $term)
            ->with('customer:id,name')
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'number' => $order->order_number,
                'customer' => $order->customer->name ?? 'Walk-in customer',
                'status' => $order->status,
                'status_label' => $order->status_label,
                'total' => money($order->total, $order->currency),
                'url' => route('orders.details', $order),
            ]);

        return response()->json([
            'exact' => $exact ? route('orders.details', $exact) : null,
            'orders' => $orders,
        ]);
    }

    /**
     * Move several orders to one status (the orders list's bulk action).
     */
    public function bulkStatus(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1|max:200',
            'ids.*' => 'integer',
            'status' => 'required|in:' . implode(',', Order::STATUSES),
        ]);

        $updated = Order::whereIn('id', $validated['ids'])
            ->where('status', '!=', $validated['status'])
            ->get()
            ->each(fn (Order $order) => $order->update(['status' => $validated['status']]))
            ->count();

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'message' => $updated === 1 ? '1 order updated.' : "{$updated} orders updated.",
        ]);
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
