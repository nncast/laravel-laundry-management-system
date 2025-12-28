<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit']);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->orderBy('id', 'asc')->get();

        $categories = Category::where('status', 1)->get();
        $units = Unit::where('status', 'active')->get();

        return view('inventory-products', compact('products', 'categories', 'units'));
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'service_type_id' => 'required|exists:service_types,id',
        'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'is_active' => 'sometimes|boolean',
    ]);

    $iconPath = null;
    if ($request->hasFile('icon_file')) {
        $iconPath = $request->file('icon_file')->store('services', 'public');
    }

    $service = Service::create([
        'name' => $request->name,
        'service_type_id' => $request->service_type_id,
        'icon' => $iconPath,
        'is_active' => $request->is_active ?? true,
    ]);

    return response()->json($service, 201);
}


    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'purchase_price' => 'required|numeric|min:0',
            'available_stock' => 'required|integer|min:0',
            'minimum_stock_level' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $product = Product::findOrFail($request->product_id);
        $product->update($request->only([
            'name', 'category_id', 'unit_id',
            'purchase_price', 'available_stock',
            'minimum_stock_level', 'status'
        ]));

        return back()->with('success', 'Product updated.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::findOrFail($request->product_id);
        $product->delete();

        return response()->json(['success' => true]);
    }
}
