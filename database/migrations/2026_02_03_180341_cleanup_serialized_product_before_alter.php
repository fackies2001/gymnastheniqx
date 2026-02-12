<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('serialized_product', function (Blueprint $table) {
            // Tanggalin ang status_temp kung nandyan
            if (Schema::hasColumn('serialized_product', 'status_temp')) {
                $table->dropColumn('status_temp');
            }
        });
    }

    public function down()
    {
        //
    }
};
