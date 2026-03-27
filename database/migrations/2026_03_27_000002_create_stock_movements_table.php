<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // ✅ Which product (points to existing supplier_product table)
            $table->foreignId('product_id')
                ->constrained('supplier_product')
                ->onDelete('cascade');

            // ✅ Which warehouse/branch (points to existing warehouse table)
            $table->foreignId('warehouse_id')
                ->constrained('warehouse')
                ->onDelete('cascade');

            // ✅ Type of movement:
            // in         = natanggap na stocks (from PO/delivery)
            // out        = ibinenta sa retailer
            // damage     = nasira
            // loss       = nawala
            // adjustment = manual correction ng stock count
            $table->enum('type', ['in', 'out', 'damage', 'loss', 'adjustment']);

            // ✅ How many pieces moved
            $table->integer('quantity');

            // ✅ Sub-reason for the movement
            // defective_on_arrival = may defect habang nire-receive (DOA)
            // damaged_in_storage   = nasira habang naka-stock
            // leaked               = nag-leak (tubig, gatas, etc)
            // expired              = nag-expire
            // lost_in_transit      = nawala sa delivery
            // missing_in_count     = kulang sa inventory count
            // sold_to_retailer     = normal na benta sa retailer
            // received_from_supplier = normal na resibo mula supplier
            // stock_correction     = manual adjustment/correction
            // other                = iba pa
            $table->enum('reason_type', [
                'defective_on_arrival',
                'damaged_in_storage',
                'leaked',
                'expired',
                'lost_in_transit',
                'missing_in_count',
                'sold_to_retailer',
                'received_from_supplier',
                'stock_correction',
                'other'
            ])->nullable();

            // ✅ Additional notes
            $table->text('remarks')->nullable();

            // ✅ Reference to Purchase Order (for IN movements)
            $table->foreignId('purchase_order_id')
                ->nullable()
                ->constrained('purchase_order')
                ->onDelete('set null');

            // ✅ Reference to Retailer Order (for OUT movements)
            $table->foreignId('retailer_order_id')
                ->nullable()
                ->constrained('retailer_orders')
                ->onDelete('set null');

            // ✅ Who made this movement (points to existing employee table)
            $table->foreignId('created_by')
                ->constrained('employee')
                ->onDelete('cascade');

            $table->timestamps();

            // ✅ Indexes para mabilis ang daily report queries
            $table->index(['product_id', 'type', 'created_at']);
            $table->index(['warehouse_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
