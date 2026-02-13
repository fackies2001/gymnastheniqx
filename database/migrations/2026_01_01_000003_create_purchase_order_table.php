<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('purchase_request_id')->constrained('purchase_request')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('supplier')->onDelete('cascade');

            // ✅ FIXED: Changed 'user' to 'employee'
            $table->foreignId('approved_by')->constrained('employee')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('employee')->onDelete('cascade');

            $table->date('order_date');
            $table->date('delivery_date');
            $table->enum('payment_terms', ['cash_on_delivery', 'bank_transfer']);
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending_scan', 'scanning', 'completed'])->default('pending_scan');
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order');
    }
};
