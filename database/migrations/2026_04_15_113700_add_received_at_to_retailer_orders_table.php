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
            if (!Schema::hasColumn('retailer_orders', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('shipped_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retailer_orders', function (Blueprint $table) {
            if (Schema::hasColumn('retailer_orders', 'received_at')) {
                $table->dropColumn('received_at');
            }
        });
    }
};
