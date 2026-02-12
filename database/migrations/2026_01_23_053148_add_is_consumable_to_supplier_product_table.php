<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('supplier_product', function (Blueprint $table) {
            $table->boolean('is_consumable')->default(0)->after('name');
        });
    }

    public function down()
    {
        Schema::table('supplier_product', function (Blueprint $table) {
            $table->dropColumn('is_consumable');
        });
    }
};
