<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SerializedProduct extends Model
{
    use HasFactory;

    protected $table = 'serialized_product';

    // ✅ BAGO - match sa actual DB columns
    protected $fillable = [
        'product_id',
        'purchase_order_id',
        'barcode',
        'serial_number',
        'status',
        'scanned_by',
        'warehouse_id',
        'remarks'
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* ========================================
       ✅ RELATIONSHIPS - FIXED
       ======================================== */

    // ✅ BAGO - tamang FK base sa actual database
    public function supplierProducts()
    {
        return $this->belongsTo(SupplierProduct::class, 'product_id', 'id');
    }

    /**
     * Relationship to PurchaseOrder
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * Relationship to Employee who scanned
     */
    public function scannedBy()
    {
        return $this->belongsTo(Employee::class, 'scanned_by', 'id');
    }

    /**
     * Relationship to Warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * ✅ FIXED: Changed foreign key from 'status' to 'product_status_id'
     */
    public function productStatus()
    {
        return $this->belongsTo(ProductStatus::class, 'status', 'id');
    }

    /* ========================================
       ✅ SCOPES
       ======================================== */

    /**
     * ✅ Count by status
     */
    public function scopeCountsPerStatus($query)
    {
        return $query->join('product_status', 'serialized_product.product_status_id', '=', 'product_status.id')
            ->select('product_status.name as name', DB::raw('count(serialized_product.id) as total'))
            ->groupBy('product_status.name');
    }

    /**
     * ✅ Monthly scanned products
     */
    public function scopeMonthlyProductScannedIn($query)
    {
        return $query->select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month');
    }
}
