<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        // Optional search functionality
        $query = $request->input('search');
        $customers = Customer::query()
        ->when($query, function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
            ->orWhere('contact', 'like', "%{$query}%")
            ->orWhere('address', 'like', "%{$query}%");
        })
        ->orderBy('id', 'desc')
        ->paginate(10); // 10 per page


        return view('customers', compact('customers'));

    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'contact' => 'nullable|regex:/^[0-9]{10,15}$/',
        'address' => 'nullable|string|max:500',
        ]);


    Customer::create($request->only('name', 'contact', 'address'));

    return redirect()->route('customers.index')->with('success', 'Customer added successfully.');
}
public function edit(Customer $customer)
{
    return response()->json($customer);
}
public function update(Request $request, Customer $customer)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'contact' => 'nullable|regex:/^[0-9]{10,15}$/',
        'address' => 'nullable|string|max:500',
    ]);

    $customer->update($validated);

    return redirect()->route('customers.index')->with('success', 'Customer updated.');
}
public function destroy(Customer $customer)
{
    try {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    } catch (\Exception $e) {
        return redirect()->route('customers.index')->with('error', 'Failed to delete customer.');
    }
}



}
