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
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'service_type_id' => 'required|exists:service_types,id',
                'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'price' => 'required|numeric|min:0',
            ]);

            $isActive = $request->has('is_active') ? 1 : 0;

            $serviceData = [
                'name' => $validated['name'],
                'service_type_id' => $validated['service_type_id'],
                'is_active' => $isActive,
                'price' => $validated['price'],
                'icon' => 'images/services/placeholder.jpg',
            ];

            if ($request->hasFile('icon_file') && $request->file('icon_file')->isValid()) {
                $file = $request->file('icon_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('services', $filename, 'public');
                $serviceData['icon'] = 'storage/' . $path;
            }

            // Ensure fillable is set in Service model
            $service = Service::create($serviceData);

            return response()->json(['success' => true, 'data' => $service]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $service = Service::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'service_type_id' => 'required|exists:service_types,id',
                'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'price' => 'required|numeric|min:0',
            ]);

            $service->name = $validated['name'];
            $service->service_type_id = $validated['service_type_id'];
            $service->price = $validated['price'];
            $service->is_active = $request->has('is_active') ? 1 : 0;

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
                $service->icon = 'images/services/placeholder.jpg';
            }

            $service->save();

            return response()->json(['success' => true, 'data' => $service]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $service = Service::findOrFail($id);

            if ($service->icon && $service->icon !== 'images/services/placeholder.jpg') {
                $oldPath = str_replace('storage/', '', $service->icon);
                Storage::disk('public')->delete($oldPath);
            }

            $service->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
