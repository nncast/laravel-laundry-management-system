<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            // Foreign keys
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // references categories.id
            $table->foreignId('unit_id')->constrained()->onDelete('cascade'); // references units.id
            
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->integer('available_stock')->default(0);
            $table->integer('minimum_stock_level')->default(0);
            $table->enum('status', ['active','inactive'])->default('active');

            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
