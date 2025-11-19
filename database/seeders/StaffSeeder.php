<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        Staff::create([
            'name' => 'Admin',
            'phone' => '09123456789',
            'username' => 'admin',
            'password' => 'admin123', // let the mutator hash it
            'role' => 'admin',
        ]);

    }
}
