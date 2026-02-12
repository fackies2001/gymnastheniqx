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
        Schema::table('purchase_order', function (Blueprint $table) {
            $table->json('received_items')->nullable()->comment('JSON array of received items...');
            // ✅ Tinanggal ang ->after('total_amount')
        });
    }

    public function down(): void
    {
        // Ganito rin sa down method
        Schema::table('purchase_order', function (Blueprint $table) {
            $table->dropColumn('received_items');
        });
    }
};
