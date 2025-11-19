<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    // List service types
    public function index()
    {
        $serviceTypes = ServiceType::orderBy('id')->get();

        // Blade file has a dash in the name, so reference it literally
        return view('services-type', compact('serviceTypes'));
    }

    // Store new service type
   public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'is_active' => 'boolean'
    ]);

    $serviceType = ServiceType::create($validated);

    // Redirect back with success message instead of JSON
    return redirect()->route('services.type')->with('success', 'Service type created successfully!');
}

public function update(Request $request)
{
    $validated = $request->validate([
        'id' => 'required|exists:service_types,id',
        'name' => 'required|string|max:255',
        'is_active' => 'boolean'
    ]);

    $serviceType = ServiceType::findOrFail($request->id);
    $serviceType->update($validated);

    return redirect()->route('services.type')->with('success', 'Service type updated successfully!');
}

public function destroy(Request $request)
{
    $request->validate([
        'id' => 'required|exists:service_types,id'
    ]);

    $serviceType = ServiceType::findOrFail($request->id);
    $serviceType->delete();

    return redirect()->route('services.type')->with('success', 'Service type deleted successfully!');
}
}
