<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retailer_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('retailer_orders', 'created_by')) {
                $table->string('created_by')->nullable();
            }
            if (!Schema::hasColumn('retailer_orders', 'created_by_user_id')) {
                $table->unsignedBigInteger('created_by_user_id')->nullable();
            }
        });

        // ✅ STEP 2: Backfill existing records
        $orders = DB::table('retailer_orders')
            ->whereNull('created_by_user_id')
            ->whereNotNull('created_by')
            ->get();

        $userTable = Schema::hasTable('employee') ? 'employee' : (Schema::hasTable('users') ? 'users' : 'user');

        foreach ($orders as $order) {
            $user = DB::table($userTable)
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
