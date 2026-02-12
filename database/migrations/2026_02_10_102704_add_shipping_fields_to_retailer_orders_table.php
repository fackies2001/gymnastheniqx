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
        Schema::table('retailer_orders', function (Blueprint $table) {
            // ✅ Add shipping tracking columns
            $table->string('shipped_by')->nullable()->after('rejected_at');
            $table->timestamp('shipped_at')->nullable()->after('shipped_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retailer_orders', function (Blueprint $table) {
            $table->dropColumn(['shipped_by', 'shipped_at']);
        });
    }
};
