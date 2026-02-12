<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check first if column doesn't exist to avoid error
        if (!Schema::hasColumn('purchase_order', 'status')) {
            Schema::table('purchase_order', function (Blueprint $table) {
                $table->enum('status', ['pending_scan', 'scanning', 'completed'])
                    ->default('pending_scan');
                // ✅ Removed ->after('remarks') kasi walang remarks column
            });
        }
    }

    public function down(): void
    {
        Schema::table('purchase_order', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_order', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
