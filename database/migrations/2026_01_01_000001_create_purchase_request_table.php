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
        Schema::create('purchase_request', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // Auto-generated
            $table->foreignId('user_id')->constrained('user')->onDelete('cascade'); // Requestor
            $table->foreignId('department_id')->constrained('department')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('supplier')->onDelete('set null');
            $table->foreignId('status_id')->default(1)->constrained('purchase_status_library')->onDelete('cascade');
            $table->date('order_date')->nullable();
            $table->date('estimated_delivery_date')->nullable();
            $table->enum('payment_terms', ['cash_on_delivery', 'bank_transfer'])->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('user')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_request');
    }
};
