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
        Schema::table('serialized_product', function (Blueprint $table) {
            // Add scanned_by column (references employee table)
            if (!Schema::hasColumn('serialized_product', 'scanned_by')) {
                $table->foreignId('scanned_by')
                    ->nullable()
                    ->after('scanned_at')
                    ->constrained('employee')
                    ->onDelete('set null');
            }

            // Add warehouse_id column (references warehouse table)
            if (!Schema::hasColumn('serialized_product', 'warehouse_id')) {
                $table->foreignId('warehouse_id')
                    ->nullable()
                    ->after('scanned_by')
                    ->constrained('warehouse')
                    ->onDelete('set null');
            }

            // Add remarks column (optional but useful)
            if (!Schema::hasColumn('serialized_product', 'remarks')) {
                $table->text('remarks')
                    ->nullable()
                    ->after('warehouse_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('serialized_product', function (Blueprint $table) {
            // Drop foreign keys first
            if (Schema::hasColumn('serialized_product', 'scanned_by')) {
                $table->dropForeign(['scanned_by']);
                $table->dropColumn('scanned_by');
            }

            if (Schema::hasColumn('serialized_product', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            }

            if (Schema::hasColumn('serialized_product', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};
