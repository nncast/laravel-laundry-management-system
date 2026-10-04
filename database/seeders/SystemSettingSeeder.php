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
                'business_name' => 'Soap Opera',
                'address'       => '123 Laundry Street, Clean City, CC 12345',
                'contact'       => '09123456789',
                'favicon'       => null, // upload an .ico under Settings > Master Setting
            ]);
        }
    }
}
