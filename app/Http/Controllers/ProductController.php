<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private const RULES = [
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'unit_id' => 'required|exists:units,id',
        'purchase_price' => 'required|numeric|min:0',
        'available_stock' => 'required|integer|min:0',
        'minimum_stock_level' => 'required|integer|min:0',
        'status' => 'required|in:active,inactive',
    ];

    public function index()
    {
        $products = Product::with(['category:id,name', 'unit:id,name,short_form'])
            ->orderBy('id')
            ->get();

        $categories = Category::where('status', 1)->orderBy('name')->get(['id', 'name']);
        $units = Unit::where('status', 'active')->orderBy('name')->get(['id', 'name', 'short_form']);

        return view('inventory-products', compact('products', 'categories', 'units'));
    }

    public function store(Request $request)
    {
        Product::create($request->validate(self::RULES));

        return redirect()->route('products.index')
            ->with('success', 'Product added successfully!');
    }

    public function update(Request $request)
    {
        $validated = $request->validate(['product_id' => 'required|exists:products,id'] + self::RULES);

        Product::findOrFail($validated['product_id'])
            ->update(collect($validated)->except('product_id')->all());

        return back()->with('success', 'Product updated.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        Product::findOrFail($request->product_id)->delete();

        return response()->json(['success' => true]);
    }
}
