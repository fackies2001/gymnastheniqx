<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('serial_number', function (Blueprint $table) {
            $table->id();

            // Nakaturo na sa singular na supplier_product
            $table->foreignId('sku_id')->nullable()->constrained('supplier_product')->onDelete('set null');

            $table->uuid('serial_number');

            $table->foreignId('product_status_id')->nullable()->constrained('product_status')->nullOnDelete();

            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_order')->nullOnDelete();

            // DAPAT GANITO: Alisan ng 's' para mag-match sa warehouse migration mo
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouse')->nullOnDelete();

            $table->foreignId('scanned_by')->nullable()->constrained('employee')->nullOnDelete();

            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('serial_number');
    }
};
