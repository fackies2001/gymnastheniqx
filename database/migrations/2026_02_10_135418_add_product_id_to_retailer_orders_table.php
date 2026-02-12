<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retailer_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('id');

            // Optional: Foreign key
            $table->foreign('product_id')
                ->references('id')
                ->on('supplier_product')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('retailer_orders', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
