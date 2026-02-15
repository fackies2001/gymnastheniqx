<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SerializedProduct extends Model
{
    use HasFactory;

    protected $table = 'serialized_product';

    protected $fillable = [
        'product_id',
        'purchase_order_id',
        'barcode',
        'serial_number',
        'status',
        'scanned_by',
        'scanned_at',
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

    // ✅ FIXED: Points to User model (which is employee table)
    public function scannedBy()
    {
        return $this->belongsTo(User::class, 'scanned_by', 'id');
    }

    public function supplierProducts()
    {
        return $this->belongsTo(SupplierProduct::class, 'product_id', 'id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function productStatus()
    {
        return $this->belongsTo(ProductStatus::class, 'status', 'id');
    }

    /* ========================================
       ✅ SCOPES
       ======================================== */

    public function scopeCountsPerStatus($query)
    {
        return $query->join('product_status', 'serialized_product.status', '=', 'product_status.id')
            ->select('product_status.name as name', DB::raw('count(serialized_product.id) as total'))
            ->groupBy('product_status.name');
    }

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
