<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use Illuminate\Http\Request;

class AddonController extends Controller
{
    // Display the addons page using Blade
    public function index()
    {
        $addons = Addon::orderBy('name')->get();
        return view('services-addons', compact('addons')); // resources/views/services-addons.blade.php
    }

    // Create a new addon (JSON response)
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'is_active' => 'nullable|boolean',
            ]);

            $addon = Addon::create([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'is_active' => $validated['is_active'] ?? 1,
            ]);

            return response()->json(['success' => true, 'data' => $addon]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Update an existing addon (JSON response)
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'is_active' => 'nullable|boolean',
            ]);

            $addon = Addon::findOrFail($id);
            $addon->update([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'is_active' => $validated['is_active'] ?? $addon->is_active,
            ]);

            return response()->json(['success' => true, 'data' => $addon]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Delete an addon (JSON response)
    public function destroy($id)
    {
        try {
            $addon = Addon::findOrFail($id);
            $addon->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Get active addons for POS (JSON response)
    public function getActiveAddons()
{
    try {
        $addons = Addon::where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'is_active'])
            ->map(function ($addon) {
                // Ensure price is a float
                $addon->price = (float) $addon->price;
                return $addon;
            });

        return response()->json([
            'success' => true,
            'addons' => $addons
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching addons: ' . $e->getMessage()
        ], 500);
    }
}

    // Alternative: Get all addons including inactive (for admin purposes)
    public function getAllAddons()
    {
        try {
            $addons = Addon::orderBy('name')
                ->get(['id', 'name', 'price', 'is_active']);

            return response()->json([
                'success' => true,
                'addons' => $addons
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching addons: ' . $e->getMessage()
            ], 500);
        }
    }
}