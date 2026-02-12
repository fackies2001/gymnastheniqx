<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('gym_equipments', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique(); // Unique code para sa equipment
            $table->string('name');                // Pangalan ng equipment
            $table->integer('quantity');           // Ilan ang stock
            $table->string('status')->default('Available'); // Status: Available, Broken, etc.
            $table->timestamps();                  // date_created at date_updated
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gym_equipments');
    }
};
