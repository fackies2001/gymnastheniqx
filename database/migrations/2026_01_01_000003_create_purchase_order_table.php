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
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique(); // Auto-generated
            $table->foreignId('purchase_request_id')->constrained('purchase_request')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('supplier')->onDelete('cascade');
            $table->foreignId('approved_by')->constrained('user')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('user')->onDelete('cascade');
            $table->date('order_date');
            $table->date('delivery_date');
            $table->enum('payment_terms', ['cash_on_delivery', 'bank_transfer']);
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending_scan', 'scanning', 'completed'])->default('pending_scan');
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order');
    }
};
