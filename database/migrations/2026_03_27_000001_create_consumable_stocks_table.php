<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumable_stocks', function (Blueprint $table) {
            $table->id();

            // ✅ Points to supplier_product table (existing)
            $table->foreignId('product_id')
                ->constrained('supplier_product')
                ->onDelete('cascade');

            // ✅ Points to warehouse table (existing)
            $table->foreignId('warehouse_id')
                ->constrained('warehouse')
                ->onDelete('cascade');

            // ✅ Current stock quantity
            $table->integer('current_qty')->default(0);

            // ✅ Minimum stock before low stock alert triggers
            $table->integer('min_stock_level')->default(20);

            $table->timestamps();

            // ✅ One record per product per warehouse only
            $table->unique(['product_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumable_stocks');
    }
};
