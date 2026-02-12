<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check muna kung exist, then drop para malinis
        Schema::dropIfExists('sales');
        // Schema::dropIfExists('products'); // Optional: Kung gusto mo linisin yung maling products table

        // Gagawa tayo ng SALES table na nakadikit sa SUPPLIER_PRODUCTS
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            // ETO ANG SUSI: 'supplier_products' ang ginamit natin dito
            $table->foreignId('product_id')->constrained('supplier_product')->onDelete('cascade');

            $table->integer('quantity');
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales');
    }
};
