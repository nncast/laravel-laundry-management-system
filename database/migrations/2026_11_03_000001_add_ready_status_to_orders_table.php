<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds "ready" (washed and waiting for the customer) between processing and
 * completed, so the dashboard can list what is ready for pickup. Going
 * straight from processing to completed still works.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processing', 'ready', 'completed', 'cancelled'])
                ->default('pending')
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('orders')->where('status', 'ready')->update(['status' => 'processing']);

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])
                ->default('pending')
                ->change();
        });
    }
};
