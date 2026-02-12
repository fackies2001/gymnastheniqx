<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supplier_product', function (Blueprint $table) {
            $table->id();

            // Relationship to your suppliers table
            $table->foreignId('supplier_id')->nullable()->constrained('supplier')->onDelete('set null');

            $table->foreignId('category_id')->nullable()->constrained('category')->onDelete('set null');

            $table->string('name')->nullable();
            // $table->unsignedBigInteger('product_id')->nullable();
            // $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            // Supplier-specific details
            $table->string('supplier_sku')->nullable(); // supplier’s own SKU
            $table->string('system_sku')->nullable(); // system SKU
            $table->decimal('cost_price', 10, 2)->nullable(); // supplier cost
            $table->decimal('discount', 10, 2)->nullable(); // supplier cost
            $table->integer('stock')->default(0)->nullable();
            $table->string('availability_status')->nullable(); // e.g. “In Stock”, “Out of Stock”
            $table->string('shipping_information')->nullable(); // e.g. “Ships in 1 month”
            $table->string('warranty_information')->nullable();
            $table->string('return_policy')->nullable();
            $table->json('dimensions')->nullable(); // optional for width, height, depth if differs
            $table->string('barcode')->nullable();
            $table->string('thumbnail')->nullable();
            $table->json('images')->nullable();

            $table->foreignId('source_id')
                ->nullable()
                ->constrained('source')
                ->nullOnDelete();

            $table->timestamps();
            // $table->json('attributes')->nullable();
            // $table->text('product_img')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_product');
    }
};
