<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    public function index()
    {
        $serviceTypes = ServiceType::withCount('services')->orderBy('id')->get();

        return view('services-type', compact('serviceTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        ServiceType::create([
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('services.type')->with('success', 'Service type created successfully!');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:service_types,id',
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $serviceType = ServiceType::findOrFail($validated['id']);
        $serviceType->update([
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active', $serviceType->is_active),
        ]);

        return redirect()->route('services.type')->with('success', 'Service type updated successfully!');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:service_types,id',
        ]);

        $serviceType = ServiceType::findOrFail($request->id);

        // Deleting would cascade-delete its services (and their order history)
        if ($serviceType->services()->exists()) {
            return redirect()->route('services.type')
                ->with('error', 'This service type still has services. Move or delete them first, or set it to Inactive.');
        }

        $serviceType->delete();

        return redirect()->route('services.type')->with('success', 'Service type deleted successfully!');
    }
}
