<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('warehouse', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name of the warehouse
            $table->string('email')->nullable(); // ✅ ADDED
            $table->string('phone', 20)->nullable(); // ✅ ADDED
            $table->string('address')->nullable(); // ✅ ADDED (more specific than location)
            $table->string('location')->nullable(); // Physical address or location name
            $table->string('assignee')->nullable(); // ✅ ADDED
            $table->text('description')->nullable(); // Optional notes about warehouse
            $table->boolean('is_active')->default(true); // Soft status flag
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse');
    }
};
