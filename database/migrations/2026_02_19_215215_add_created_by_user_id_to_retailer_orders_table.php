<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ STEP 1: Add column lang — walang foreign key constraint
        // (Inalis ang foreign key para maiwasan ang engine compatibility issue)
        Schema::table('retailer_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by_user_id')
                ->nullable()
                ->after('created_by');
        });

        // ✅ STEP 2: Backfill existing records
        // I-match ang existing created_by (full_name) sa users table
        $orders = DB::table('retailer_orders')
            ->whereNull('created_by_user_id')
            ->whereNotNull('created_by')
            ->get();

        foreach ($orders as $order) {
            $user = DB::table('users')
                ->where('full_name', $order->created_by)
                ->first();

            if ($user) {
                DB::table('retailer_orders')
                    ->where('id', $order->id)
                    ->update(['created_by_user_id' => $user->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('retailer_orders', function (Blueprint $table) {
            $table->dropColumn('created_by_user_id');
        });
    }
};
