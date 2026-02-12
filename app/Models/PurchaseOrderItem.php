<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $table = 'purchase_order_items';

    protected $fillable = [
        'purchase_order_id',
        'product_id', // ✅ References supplier_product.id
        'quantity_ordered',
        'quantity_scanned',
        'unit_cost',
        'subtotal',
    ];

    protected $casts = [
        'quantity_ordered' => 'integer',
        'quantity_scanned' => 'integer',
        'unit_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    // ✅ MAIN RELATIONSHIP
    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class, 'product_id', 'id');
    }

    // ✅ ALIAS
    public function product(): BelongsTo
    {
        return $this->supplierProduct();
    }

    // ✅ ACCESSOR
    public function getProductNameAttribute()
    {
        return $this->supplierProduct->name ?? 'Unknown Product';
    }

    public function getSkuAttribute()
    {
        return $this->supplierProduct->system_sku ??
            $this->supplierProduct->supplier_sku ??
            'No SKU';
    }
}
