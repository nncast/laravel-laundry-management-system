<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    private const PLACEHOLDER = 'images/services/placeholder.jpg';

    private const RULES = [
        'name' => 'required|string|max:255',
        'service_type_id' => 'required|exists:service_types,id',
        'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        'price' => 'required|numeric|min:0',
        'is_active' => 'nullable|boolean',
    ];

    public function index()
    {
        $services = Service::with('serviceType:id,name')->orderBy('name')->get();
        $serviceTypes = ServiceType::orderBy('name')->get();

        return view('services-list', compact('services', 'serviceTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(self::RULES);

        $service = Service::create([
            'name' => $validated['name'],
            'service_type_id' => $validated['service_type_id'],
            'is_active' => $request->boolean('is_active', true),
            'price' => $validated['price'],
            'icon' => $request->hasFile('icon_file')
                ? $this->storeIcon($request->file('icon_file'))
                : self::PLACEHOLDER,
        ]);

        return response()->json(['success' => true, 'data' => $service]);
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $validated = $request->validate(self::RULES);

        $service->name = $validated['name'];
        $service->service_type_id = $validated['service_type_id'];
        $service->price = $validated['price'];
        // The form sends 1/0 from a select, so read the value (not just its presence)
        $service->is_active = $request->boolean('is_active', (bool) $service->is_active);

        if ($request->hasFile('icon_file')) {
            $this->deleteIcon($service->icon);
            $service->icon = $this->storeIcon($request->file('icon_file'));
        } elseif (!$service->icon) {
            $service->icon = self::PLACEHOLDER;
        }

        $service->save();

        return response()->json(['success' => true, 'data' => $service]);
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);

        // Deleting would cascade-delete this service from every past order
        if (OrderItem::where('service_id', $service->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This service is used in existing orders and cannot be deleted. Set it to Inactive to hide it from the POS.',
            ], 422);
        }

        $this->deleteIcon($service->icon);
        $service->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Save the icon under public/images/services so it works without `php artisan storage:link`.
     */
    private function storeIcon($file): string
    {
        $dir = public_path('images/services');
        File::ensureDirectoryExists($dir);

        $name = $file->hashName();
        $file->move($dir, $name);

        return 'images/services/' . $name;
    }

    private function deleteIcon(?string $icon): void
    {
        if (!$icon || $icon === self::PLACEHOLDER) {
            return;
        }

        if (str_starts_with($icon, 'storage/')) {
            Storage::disk('public')->delete(substr($icon, strlen('storage/')));
        } elseif (str_starts_with($icon, 'images/services/')) {
            File::delete(public_path($icon));
        }
    }
}
