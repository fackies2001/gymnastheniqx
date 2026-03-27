<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        //  FIX: Check muna kung wala pa ang column bago mag-add
        // Kasi manually na nating dinagdag sa HeidiSQL — hindi na crash kapag existing
        if (!Schema::hasColumn('supplier_product', 'selling_price')) {
            Schema::table('supplier_product', function (Blueprint $table) {
                $table->decimal('selling_price', 10, 2)->nullable()->after('cost_price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('supplier_product', 'selling_price')) {
            Schema::table('supplier_product', function (Blueprint $table) {
                $table->dropColumn('selling_price');
            });
        }
    }
};
