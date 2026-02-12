<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('warehouse_id')->nullable(); // <-- Make nullable for set null

            $table->string('serial_number')->nullable();
            $table->string('sku')->unique();
            $table->string('status')->default('in-stock');
            $table->date('storage_start_date')->nullable();
            $table->date('storage_end_date')->nullable();
            $table->date('entry_date')->nullable();

            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
