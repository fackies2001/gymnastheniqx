<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serialized_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('supplier_product', 'id')
                ->onDelete('cascade');
            $table->foreignId('purchase_order_id')
                ->nullable()
                ->constrained('purchase_order')
                ->onDelete('set null');

            // ✅ FIXED: Remove ->unique() from barcode
            $table->string('barcode')->index(); // Just index, not unique

            // ✅ Only serial_number should be unique
            $table->string('serial_number')->unique();

            $table->enum('status', ['pending', 'scanned', 'in_inventory', 'sold', 'damaged'])
                ->default('pending');
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();

            // ✅ Removed redundant ->index('barcode')
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serialized_product');
    }
};
