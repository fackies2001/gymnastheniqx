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
        // Mula sa 'serial_numbers', gawin mong 'serial_number'
        Schema::table('serial_number', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('product_status_id');
        });
    }

    public function down(): void
    {
        // Ganito rin sa down method
        Schema::table('serial_number', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};
