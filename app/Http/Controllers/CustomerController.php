<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private const RULES = [
        'name' => 'required|string|max:255',
        'contact' => ['nullable', 'regex:/^[0-9]{10,15}$/'],
        'address' => 'nullable|string|max:500',
    ];

    private const MESSAGES = [
        'contact.regex' => 'Contact number must be 10 to 15 digits.',
    ];

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
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('customers', compact('customers', 'search'));
    }

    /**
     * Lightweight lookup used by the POS (JSON).
     */
    public function search(Request $request)
    {
        $term = trim((string) $request->input('q', $request->input('search', '')));

        $customers = Customer::query()
            ->when($term !== '', fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('contact', 'like', "%{$term}%"))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'contact']);

        return response()->json(['success' => true, 'customers' => $customers]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(self::RULES, self::MESSAGES);

        $customer = Customer::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer added successfully.',
                'customer' => $customer->only(['id', 'name', 'contact', 'address']),
            ], 201);
        }

        return redirect()->route('customers.index')->with('success', 'Customer added successfully.');
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate(self::RULES, self::MESSAGES);

        $customer->update($validated);

        return redirect()->back()->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        // Orders keep their history (customer_id is set to NULL → "Walk-in Customer")
        $customer->delete();

        return redirect()->back()->with('success', 'Customer deleted successfully.');
    }
}
