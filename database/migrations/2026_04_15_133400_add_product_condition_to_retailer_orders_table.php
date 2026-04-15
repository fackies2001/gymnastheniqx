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
        if (!Schema::hasColumn('retailer_orders', 'product_condition')) {
            Schema::table('retailer_orders', function (Blueprint $table) {
                $table->string('product_condition')->default('Standard')->after('product_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('retailer_orders', 'product_condition')) {
            Schema::table('retailer_orders', function (Blueprint $table) {
                $table->dropColumn('product_condition');
            });
        }
    }
};
