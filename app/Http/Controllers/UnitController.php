<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    private const RULES = [
        'name' => 'required|string|max:255',
        'short_form' => 'nullable|string|max:10',
        'description' => 'nullable|string|max:1000',
        'status' => 'required|in:active,inactive',
    ];

    public function index()
    {
        $units = Unit::orderBy('id')->get();

        return view('inventory-units', compact('units'));
    }

    public function store(Request $request)
    {
        $unit = Unit::create($request->validate(self::RULES));

        return response()->json([
            'success' => true,
            'unit' => $unit,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate(['id' => 'required|exists:units,id'] + self::RULES);

        $unit = Unit::findOrFail($validated['id']);
        $unit->update(collect($validated)->except('id')->all());

        return response()->json([
            'success' => true,
            'unit' => $unit,
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:units,id',
        ]);

        $unit = Unit::findOrFail($request->id);

        // Deleting would cascade-delete every product using this unit
        if ($unit->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This unit is used by products. Change those products first, or set the unit to Inactive.',
            ], 422);
        }

        $unit->delete();

        return response()->json(['success' => true]);
    }
}
