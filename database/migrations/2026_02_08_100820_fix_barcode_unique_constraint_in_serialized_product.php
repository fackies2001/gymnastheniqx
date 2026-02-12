<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('serialized_product', function (Blueprint $table) {
            // ✅ Drop the unique constraint
            $table->dropUnique('serialized_product_barcode_unique');

            // ✅ Keep it as regular index for search performance
            // (index already exists from the original migration)
        });
    }

    public function down(): void
    {
        Schema::table('serialized_product', function (Blueprint $table) {
            // Restore unique constraint if rolled back
            $table->unique('barcode', 'serialized_product_barcode_unique');
        });
    }
};
