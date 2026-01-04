<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Only create default settings if none exist
        if (!SystemSetting::exists()) {
            SystemSetting::create([
                'business_name' => 'LAUNDRY',
                'address'       => '123 Laundry Street, Clean City, CC 12345',
                'contact'       => '09123456789',
                'favicon'       => 'favicons.ico', // place a default favicon in storage/app/public/favicons
            ]);
        }
    }
}
