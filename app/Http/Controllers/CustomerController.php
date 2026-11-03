<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private const RULES = [
        'name' => 'required|string|max:255',
        'contact' => ['nullable', 'regex:/^[0-9]{10,15}$/'],
        'address' => 'nullable|string|max:500',
    ];

    private function messages(): array
    {
        return ['contact.regex' => __('Contact number must be 10 to 15 digits.')];
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $customers = Customer::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('contact', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            })
            // What the counter wants to know at a glance: how often they come and what they owe
            ->withCount('orders')
            ->addSelect([
                'balance_due' => Order::selectRaw('COALESCE(SUM(total - paid_amount), 0)')
                    ->whereColumn('customer_id', 'customers.id')->unpaid()->inBusinessCurrency(),
                'last_order_date' => Order::select('order_date')
                    ->whereColumn('customer_id', 'customers.id')->orderByDesc('order_date')->limit(1),
            ])
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('customers', compact('customers', 'search'));
    }

    /**
     * Type-ahead for the POS (JSON): names containing the term, phone numbers
     * starting with its digits (indexed), alphabetical, a handful at a time.
     */
    public function search(Request $request)
    {
        $term = trim((string) $request->input('q', $request->input('search', '')));
        $digits = preg_replace('/\D/', '', $term);

        // Names that start with the term first (alphabetical, from the name index), then
        // names containing it and phone numbers starting with its digits (newest first)
        $columns = ['id', 'name', 'contact'];
        $customers = Customer::where('name', 'like', "{$term}%")->orderBy('name')->limit(8)->get($columns);
        if ($term !== '' && $customers->count() < 8) {
            $customers = $customers->concat(Customer::query()
                ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                    ->when(strlen($digits) >= 3, fn ($q) => $q->orWhere('contact', 'like', "{$digits}%")))
                ->whereNotIn('id', $customers->pluck('id'))
                ->orderByDesc('id')
                ->limit(8 - $customers->count())
                ->get($columns));
        }

        return response()->json(['success' => true, 'customers' => $customers]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(self::RULES, $this->messages());

        $customer = Customer::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Customer added successfully.'),
                'customer' => $customer->only(['id', 'name', 'contact', 'address']),
            ], 201);
        }

        return redirect()->route('customers.index')->with('success', __('Customer added successfully.'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate(self::RULES, $this->messages());

        $customer->update($validated);

        return redirect()->back()->with('success', __('Customer updated.'));
    }

    public function destroy(Customer $customer)
    {
        // Orders keep their history (customer_id is set to NULL → "Walk-in Customer")
        $customer->delete();

        return redirect()->back()->with('success', __('Customer deleted successfully.'));
    }
}
