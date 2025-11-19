<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::orderBy('id')->get();
        return view('inventory-units', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_form' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $unit = Unit::create($request->only('name','short_form','description','status'));

        return response()->json([
            'success' => true,
            'unit' => $unit
        ]);
    }



    // In your UnitController
public function update(Request $request)
{
    $request->validate([
        'id' => 'required|exists:units,id',
        'name' => 'required|string|max:255',
        'short_form' => 'nullable|string|max:10',
        'description' => 'nullable|string',
        'status' => 'required|in:active,inactive'
    ]);

    $unit = Unit::find($request->id);
    $unit->update($request->all());

    return response()->json([
        'success' => true,
        'unit' => $unit
    ]);
}

public function destroy(Request $request)
{
    $request->validate([
        'id' => 'required|exists:units,id'
    ]);

    $unit = Unit::find($request->id);
    $unit->delete();

    return response()->json(['success' => true]);
}


}