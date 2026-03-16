<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * ✅ Nagdadagdag ng selling_price column sa supplier_product table
     * Ito ang presyo ng ibebenta sa retailer (dapat mas mataas sa cost_price)
     */
    public function up(): void
    {
        Schema::table('supplier_product', function (Blueprint $table) {
            // ✅ Idadagdag pagkatapos ng cost_price column
            $table->decimal('selling_price', 10, 2)->nullable()->after('cost_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_product', function (Blueprint $table) {
            $table->dropColumn('selling_price');
        });
    }
};
