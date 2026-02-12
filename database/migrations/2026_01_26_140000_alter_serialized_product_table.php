<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add temporary integer column (with check)
        if (!Schema::hasColumn('serialized_product', 'status_temp')) {
            Schema::table('serialized_product', function (Blueprint $table) {
                $table->unsignedBigInteger('status_temp')->nullable()->after('status');
            });
        }

        // Step 2: Convert old string values to new integer values
        DB::statement("
            UPDATE serialized_product 
            SET status_temp = CASE 
                WHEN CAST(status AS CHAR) = 'pending' THEN 1
                WHEN CAST(status AS CHAR) = 'scanned' THEN 1
                WHEN CAST(status AS CHAR) = 'in_inventory' THEN 1
                WHEN CAST(status AS CHAR) = 'sold' THEN 3
                WHEN CAST(status AS CHAR) = 'damaged' THEN 4
                WHEN status = 1 THEN 1
                WHEN status = 2 THEN 2
                WHEN status = 3 THEN 3
                WHEN status = 4 THEN 4
                ELSE 1
            END
        ");

        // Step 3: Drop old column (with check)
        if (Schema::hasColumn('serialized_product', 'status')) {
            Schema::table('serialized_product', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        // Step 4: Rename temp column (with check)
        if (
            Schema::hasColumn('serialized_product', 'status_temp') &&
            !Schema::hasColumn('serialized_product', 'status')
        ) {
            Schema::table('serialized_product', function (Blueprint $table) {
                $table->renameColumn('status_temp', 'status');
            });
        }

        // Step 5: Add foreign key constraint
        // Simple check - try to add, catch if exists
        try {
            Schema::table('serialized_product', function (Blueprint $table) {
                $table->foreign('status')
                    ->references('id')
                    ->on('product_status')
                    ->onDelete('restrict');
            });
        } catch (\Exception $e) {
            // Foreign key already exists, ignore
        }
    }

    public function down(): void
    {
        try {
            Schema::table('serialized_product', function (Blueprint $table) {
                $table->dropForeign(['status']);
            });
        } catch (\Exception $e) {
            // Ignore if foreign key doesn't exist
        }

        Schema::table('serialized_product', function (Blueprint $table) {
            $table->string('status', 50)->change();
        });
    }
};
