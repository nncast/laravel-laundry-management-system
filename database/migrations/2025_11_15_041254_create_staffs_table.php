<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staffs', function (Blueprint $table) { // changed from 'staff' to 'users'
            $table->id();

            $table->string('name');                // Full name
            $table->string('phone')->nullable();   // Phone number
            $table->string('username')->unique();  // Username for login
            $table->string('password');            // Hashed password

            $table->enum('role', ['admin', 'manager', 'cashier'])
                  ->default('cashier');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staffs');
    }
};

