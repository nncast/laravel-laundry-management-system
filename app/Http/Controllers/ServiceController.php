<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    // Display all services
    public function index()
    {
        $services = Service::with('serviceType')->get();
        $serviceTypes = ServiceType::all();
        return view('services-list', compact('services', 'serviceTypes'));
    }

    // Store a new service
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'service_type_id' => 'required|exists:service_types,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $service = Service::create([
            'uuid' => Str::uuid(),
            'name' => $request->name,
            'icon' => $request->icon,
            'service_type_id' => $request->service_type_id,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json($service, 201);
    }

    // Update a service (ID in payload)
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:services,id',
            'name' => 'sometimes|string|max:255',
            'icon' => 'sometimes|string|max:255',
            'service_type_id' => 'sometimes|exists:service_types,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $service = Service::findOrFail($request->id);
        $service->update($request->only(['name', 'icon', 'service_type_id', 'is_active']));

        return response()->json($service);
    }

    // Delete a service (ID in payload)
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:services,id',
        ]);

        $service = Service::findOrFail($request->id);
        $service->delete();

        return response()->json(['message' => 'Service deleted successfully']);
    }
}
