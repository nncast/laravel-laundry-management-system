<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{

    public function index()
{
    $settings = SystemSetting::first();
    return response()->json($settings);
}

    public function edit()
{
    $settings = SystemSetting::first();
    return view('settings-mastersettings', compact('settings'));
}


    public function update(Request $request)
    {
        $request->validate([
            'business_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'contact' => 'nullable|string|max:255',
            'favicon' => 'nullable|file|mimes:ico' // validation only allows .ico
        ]);

        $settings = SystemSetting::first() ?? new SystemSetting();

        $settings->business_name = $request->business_name;
        $settings->address = $request->address;
        $settings->contact = $request->contact;

       if ($request->hasFile('favicon')) {
                $file = $request->file('favicon');

                // force overwrite public/favicon.ico
                $file->move(public_path(), 'favicon.ico');

                // store only the filename if you want to save the path
                $settings->favicon = 'favicon.ico';
            }


        $settings->save();

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
