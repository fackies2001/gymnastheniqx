<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee', function (Blueprint $table) {
            // I-add ang password kung wala pa
            if (!Schema::hasColumn('employee', 'password')) {
                $table->string('password')->nullable()->after('username');
            }

            // I-add ang remember_token kung wala pa
            if (!Schema::hasColumn('employee', 'remember_token')) {
                $table->rememberToken()->after('password');
            }

            // I-add ang pin kung wala pa
            if (!Schema::hasColumn('employee', 'pin')) {
                $table->string('pin', 255)->nullable()->after('remember_token'); // ✅ CHANGED from 6 to 255
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee', function (Blueprint $table) {
            if (Schema::hasColumn('employee', 'password')) {
                $table->dropColumn('password');
            }
            if (Schema::hasColumn('employee', 'remember_token')) {
                $table->dropColumn('remember_token');
            }
            if (Schema::hasColumn('employee', 'pin')) {
                $table->dropColumn('pin');
            }
        });
    }
};
