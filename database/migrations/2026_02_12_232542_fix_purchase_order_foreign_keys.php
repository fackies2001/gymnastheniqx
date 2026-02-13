<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order', function (Blueprint $table) {
            // ✅ Drop old foreign keys that reference 'user' table
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['requested_by']);
        });

        Schema::table('purchase_order', function (Blueprint $table) {
            // ✅ Recreate foreign keys to reference 'employee' table
            $table->foreign('approved_by')
                ->references('id')
                ->on('employee')
                ->onDelete('cascade');

            $table->foreign('requested_by')
                ->references('id')
                ->on('employee')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order', function (Blueprint $table) {
            // Revert back if needed
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['requested_by']);
        });

        Schema::table('purchase_order', function (Blueprint $table) {
            $table->foreign('approved_by')
                ->references('id')
                ->on('user')
                ->onDelete('cascade');

            $table->foreign('requested_by')
                ->references('id')
                ->on('user')
                ->onDelete('cascade');
        });
    }
};
