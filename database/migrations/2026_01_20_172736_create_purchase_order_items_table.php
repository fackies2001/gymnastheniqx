<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();

            // 1. Dito natin ilalagay ang 'purchase_order' (singular) sa loob ng constrained
            $table->foreignId('purchase_order_id')->constrained('purchase_order')->onDelete('cascade');

            // 2. Dito naman, siguraduhin na 'supplier_product' (singular) din ito 
            // gaya ng inayos natin kanina
            $table->foreignId('product_id')->constrained('supplier_product')->onDelete('cascade');

            $table->integer('quantity_ordered');
            $table->integer('quantity_scanned')->default(0);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
