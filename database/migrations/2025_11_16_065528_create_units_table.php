<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id(); // # column
            $table->string('name'); // full name
            $table->string('short_form')->nullable(); // short form
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active'); // status
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
