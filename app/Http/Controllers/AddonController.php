<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddonController extends Controller
{
    private const RULES = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'is_active' => 'nullable|boolean',
    ];

    public function index()
    {
        $addons = Addon::orderBy('name')->get();

        return view('services-addons', compact('addons'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(self::RULES);

        $addon = Addon::create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json(['success' => true, 'data' => $addon]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate(self::RULES);

        $addon = Addon::findOrFail($id);
        $addon->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'is_active' => $request->boolean('is_active', $addon->is_active),
        ]);

        return response()->json(['success' => true, 'data' => $addon]);
    }

    public function destroy($id)
    {
        $addon = Addon::findOrFail($id);

        // Deleting would cascade-delete this add-on from every past order
        if (DB::table('order_addons')->where('addon_id', $addon->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This add-on is used in existing orders and cannot be deleted. Set it to Inactive to hide it from the POS.',
            ], 422);
        }

        $addon->delete();

        return response()->json(['success' => true]);
    }
}
