<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            // Try to drop the unique constraint if it exists
            DB::statement('ALTER TABLE serialized_product DROP INDEX serialized_product_barcode_unique');
        } catch (\Exception $e) {
            // Constraint doesn't exist, that's fine - continue
        }
    }

    public function down(): void
    {
        Schema::table('serialized_product', function (Blueprint $table) {
            // Restore unique constraint if rolled back
            $table->unique('barcode', 'serialized_product_barcode_unique');
        });
    }
};
