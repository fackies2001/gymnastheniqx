<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier', function (Blueprint $table) {
            $table->string('supplier_code')->nullable()->after('id');
        });

        //  Fix existing NULL or duplicate supplier_codes bago mag-unique
        $suppliers = DB::table('supplier')
            ->orderBy('id')
            ->get();

        foreach ($suppliers as $supplier) {
            DB::table('supplier')
                ->where('id', $supplier->id)
                ->update([
                    'supplier_code' => 'SUP-' . str_pad($supplier->id, 4, '0', STR_PAD_LEFT)
                ]);
        }

        //  Safe na mag-add ng unique AFTER na na-fix ang data
        Schema::table('supplier', function (Blueprint $table) {
            $table->unique('supplier_code');
        });
    }

    public function down(): void
    {
        Schema::table('supplier', function (Blueprint $table) {
            $table->dropUnique(['supplier_code']);
            $table->dropColumn('supplier_code');
        });
    }
};
