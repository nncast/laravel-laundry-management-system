<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('id')->get();

        return view('inventory-categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'status' => 'required|boolean',
        ]);

        Category::create($validated);

        return back()->with('success', 'Category added.');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:categories,name,' . (int) $request->category_id,
            'status' => 'required|boolean',
        ]);

        Category::findOrFail($validated['category_id'])
            ->update($request->only('name', 'status'));

        return back()->with('success', 'Category updated.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
        ]);

        $category = Category::findOrFail($request->category_id);

        // Deleting would cascade-delete every product in the category
        if ($category->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This category still has products. Move or delete them first, or set the category to Inactive.',
            ], 422);
        }

        $category->delete();

        return response()->json(['success' => true]);
    }
}
