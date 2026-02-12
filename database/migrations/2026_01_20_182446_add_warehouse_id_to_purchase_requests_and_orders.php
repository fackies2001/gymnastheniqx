<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Gawing 'purchase_request' (Singular)
        Schema::table('purchase_request', function (Blueprint $table) {
            $table->foreignId('warehouse_id')
                ->nullable()
                ->constrained('warehouse') // 2. Gawing 'warehouse' (Singular)
                ->onDelete('set null');
        });

        // 3. Gawing 'purchase_order' (Singular)
        Schema::table('purchase_order', function (Blueprint $table) {
            $table->foreignId('warehouse_id')
                ->nullable()
                ->constrained('warehouse') // 4. Gawing 'warehouse' (Singular)
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_request', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });

        Schema::table('purchase_order', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
    }
};
