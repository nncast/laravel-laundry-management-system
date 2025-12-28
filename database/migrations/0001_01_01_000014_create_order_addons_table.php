<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->cascadeOnDelete();
            $table->foreignId('addon_id')
                  ->constrained('addons')
                  ->cascadeOnDelete();
            $table->decimal('price', 10, 2); // price at the time of order
            $table->timestamps();

            // Optional: prevent duplicate addon for the same order
            $table->unique(['order_id', 'addon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_addons');
    }
};
