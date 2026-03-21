<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_product', function (Blueprint $table) {
            $table->unsignedInteger('pieces_per_box')
                ->default(1)
                ->after('is_consumable')
                ->comment('Ilang pieces ang nasa loob ng isang box. Default 1 = piece-level.');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_product', function (Blueprint $table) {
            $table->dropColumn('pieces_per_box');
        });
    }
};