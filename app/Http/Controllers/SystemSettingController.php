<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SystemSettingController extends Controller
{
    public function edit()
    {
        $settings = SystemSetting::first() ?? new SystemSetting();
        $lastBackup = BackupController::lastBackupTime();

        return view('settings-mastersettings', compact('settings', 'lastBackup'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:1000',
            'contact' => ['nullable', 'regex:/^[0-9]{10,15}$/'],
            // .ico files are often reported with a generic MIME type, so check the extension
            'favicon' => 'nullable|file|extensions:ico|max:200',
        ], [
            'contact.regex' => 'Contact number must be 10 to 15 digits.',
            'favicon.extensions' => 'The logo must be an .ico file.',
            'favicon.max' => 'The logo must be smaller than 200 KB.',
        ]);

        $settings = SystemSetting::first() ?? new SystemSetting();

        $settings->business_name = $validated['business_name'];
        $settings->address = $validated['address'] ?? null;
        $settings->contact = $validated['contact'] ?? null;

        if ($request->hasFile('favicon')) {
            $dir = public_path('images');
            File::ensureDirectoryExists($dir);

            try {
                $request->file('favicon')->move($dir, 'favicon.ico');
            } catch (\Throwable $e) {
                Log::error('Favicon upload failed: ' . $e->getMessage());

                return back()->withInput()
                    ->with('error', 'Settings were not saved: the logo could not be uploaded (' . $e->getMessage() . ').');
            }

            $settings->favicon = 'images/favicon.ico';
        }

        $settings->save();
        app()->forgetInstance('system.settings');

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
