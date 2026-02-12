<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ✅ SAFE MIGRATION - Adds missing columns to serialized_product
 * 
 * Adds:
 * - scanned_by (foreign key to employee)
 * - warehouse_id (foreign key to warehouse)
 * - remarks (text field)
 * 
 * Safe because:
 * - Checks if columns exist before adding
 * - All columns nullable (won't break existing data)
 * - Uses soft deletes for foreign keys
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('serialized_product', function (Blueprint $table) {
            // ✅ Add scanned_by column (references employee table)
            if (!Schema::hasColumn('serialized_product', 'scanned_by')) {
                $table->foreignId('scanned_by')
                    ->nullable()
                    ->after('scanned_at')
                    ->constrained('employee')
                    ->onDelete('set null')
                    ->comment('Employee who scanned this item');
            }

            // ✅ Add warehouse_id column (references warehouse table)
            if (!Schema::hasColumn('serialized_product', 'warehouse_id')) {
                $table->foreignId('warehouse_id')
                    ->nullable()
                    ->after('scanned_by')
                    ->constrained('warehouse')
                    ->onDelete('set null')
                    ->comment('Current warehouse location');
            }

            // ✅ Add remarks column (optional text field)
            if (!Schema::hasColumn('serialized_product', 'remarks')) {
                $table->text('remarks')
                    ->nullable()
                    ->after('warehouse_id')
                    ->comment('Additional notes or remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('serialized_product', function (Blueprint $table) {
            // Drop foreign keys first, then columns

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
