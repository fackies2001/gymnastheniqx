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
        // Mula sa 'users', gawin mong 'user'
        Schema::table('user', function (Blueprint $table) {
            $table->string('address')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        // Ganito rin sa down method
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }
};
