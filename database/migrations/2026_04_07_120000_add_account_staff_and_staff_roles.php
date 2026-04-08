<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['account staff', 'staff'] as $roleName) {
            $exists = DB::table('role')
                ->whereRaw('LOWER(TRIM(role_name)) = ?', [strtolower($roleName)])
                ->exists();
            if (!$exists) {
                DB::table('role')->insert([
                    'role_name'  => $roleName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('role')
            ->whereRaw('LOWER(TRIM(role_name)) IN (?, ?)', ['account staff', 'staff'])
            ->delete();
    }
};
