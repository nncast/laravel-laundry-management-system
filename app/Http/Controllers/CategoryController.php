<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }


        $categories = $query->orderBy('id', 'asc')->get();


        return view('inventory-categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
        'name' => 'required|max:255|unique:categories,name',
        'status' => 'required|boolean',
    ]);

    Category::create($request->only('name', 'status'));

    return back()->with('success', 'Category added.');
    }

    public function update(Request $request)
    {
        $request->validate([
        'category_id' => 'required|exists:categories,id',
        'name' => 'required|max:255|unique:categories,name,' . $request->category_id,
        'status' => 'required|boolean',
    ]);

    $category = Category::findOrFail($request->category_id);
    $category->update($request->only('name', 'status'));

    return back()->with('success', 'Category updated.');
    }

    public function destroy(Request $request)
{
    $request->validate([
        'category_id' => 'required|exists:categories,id',
    ]);

    $category = Category::findOrFail($request->category_id);
    $category->delete();

    return response()->json(['success' => true]);
}

}
