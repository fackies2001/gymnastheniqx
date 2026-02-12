<?php
/*

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SerializedProduct extends Model
{
    use HasFactory;

    protected $table = 'serialized_product';

    // ✅ UPDATED: Complete fillable fields including warehouse_id and remarks
    protected $fillable = [
        'product_id',
        'purchase_order_id',
        'barcode',
        'serial_number',
        'status',
        'scanned_by',
        'scanned_at',
        'warehouse_id',    // ✅ ADDED
        'remarks'          // ✅ ADDED
    ];

    // ✅ Relationship to SupplierProduct
    public function supplierProducts()
    {
        return $this->belongsTo(SupplierProduct::class, 'product_id');
    }

    // ✅ Relationship to PurchaseOrder
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    // ✅ FIXED: scannedBy should relate to Employee model (not User)
    // Assuming your Employee model has id as primary key
    public function scannedBy()
    {
        return $this->belongsTo(Employee::class, 'scanned_by', 'id');
    }

    // ✅ ADDED: Warehouse relationship for Location
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    // ✅ Relationship for product status
    public function productStatus()
    {
        return $this->belongsTo(ProductStatus::class, 'status');
    }
}

27-01-2026