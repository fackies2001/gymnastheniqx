<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_audits', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->integer('system_count');
            $table->integer('actual_count');
            $table->integer('variance');                            // actual - system (+ surplus, - missing)
            $table->enum('status', ['Match', 'Missing', 'Surplus']);
            $table->string('audit_period');                         // e.g. "Feb 17, 2026 - Feb 24, 2026"
            $table->string('audited_by')->nullable();               // logged-in user full_name
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_audits');
    }
};
