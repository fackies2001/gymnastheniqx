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
        Schema::create('coaches', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->date('birth_date');
            $table->text('address')->nullable();
            $table->string('contact_no');
            $table->string('email')->unique();
            $table->string('position'); // Head Coach, Senior Coach, etc.
            $table->date('date_hired');
            $table->string('status')->default('Active'); // Active, Inactive, On Leave
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaches');
    }
};
