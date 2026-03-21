<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('serialized_product', function (Blueprint $table) {
            $table->dropUnique(['serial_number']);
            $table->index('serial_number');
        });
    }

    public function down(): void
    {
        Schema::table('serialized_product', function (Blueprint $table) {
            $table->dropIndex(['serial_number']);
            $table->unique('serial_number');
        });
    }
};
