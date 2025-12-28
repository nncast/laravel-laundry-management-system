<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('serviceType')->get();
        $serviceTypes = ServiceType::all();
        return view('services-list', compact('services', 'serviceTypes'));
    }

    public function store(Request $request)
    {
        $isActive = $request->has('is_active') ? 1 : 0;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'service_type_id' => 'required|exists:service_types,id',
            'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $serviceData = [
            'name' => $validated['name'],
            'service_type_id' => $validated['service_type_id'],
            'is_active' => $isActive,
            'icon' => 'images/services/placeholder.jpg',
        ];

        if ($request->hasFile('icon_file') && $request->file('icon_file')->isValid()) {
            $file = $request->file('icon_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('services', $filename, 'public');
            $serviceData['icon'] = 'storage/' . $path;
        }

        $service = Service::create($serviceData);

        return response()->json([
            'success' => true,
            'data' => $service
        ]);
    }

    public function update(Request $request)
{
    // Find existing service
    $service = Service::findOrFail($request->id);

    // Determine active status
    $isActive = $request->has('is_active') ? 1 : 0;

    // Validate request
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'service_type_id' => 'required|exists:service_types,id',
        'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    // Update basic fields
    $service->name = $validated['name'];
    $service->service_type_id = $validated['service_type_id'];
    $service->is_active = $isActive;

    // Handle icon upload
    if ($request->hasFile('icon_file') && $request->file('icon_file')->isValid()) {
        // Delete old icon if not placeholder
        if ($service->icon && $service->icon !== 'images/services/placeholder.jpg') {
            $oldPath = str_replace('storage/', '', $service->icon);
            Storage::disk('public')->delete($oldPath);
        }

        $file = $request->file('icon_file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('services', $filename, 'public');
        $service->icon = 'storage/' . $path;
    } elseif (!$service->icon) {
        // Ensure a default placeholder if icon is null
        $service->icon = 'images/services/placeholder.jpg';
    }

    // Save updated service
    $service->save();

    return response()->json([
        'success' => true,
        'data' => $service
    ]);
}


    public function destroy(Request $request)
    {
        $service = Service::findOrFail($request->id);

        if ($service->icon && $service->icon !== 'images/services/placeholder.jpg') {
            $oldPath = str_replace('storage/', '', $service->icon);
            Storage::disk('public')->delete($oldPath);
        }

        $service->delete();

        return response()->json(['success' => true]);
    }
}
