<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequestItem extends Model
{
    protected $table = 'purchase_request_items';

    protected $fillable = [
        'purchase_request_id',
        'product_id', // ✅ This is the ONLY column in your DB (references supplier_product.id)
        'quantity',
        'unit_cost',
        'subtotal',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // ✅ MAIN RELATIONSHIP - product_id → supplier_product.id
    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class, 'product_id', 'id');
    }

    // ✅ ALIAS for convenience (same as supplierProduct)
    public function product(): BelongsTo
    {
        return $this->supplierProduct();
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    // ✅ ACCESSOR: Direct access to product name
    public function getProductNameAttribute()
    {
        return $this->supplierProduct->name ?? 'Unknown Product';
    }

    // ✅ ACCESSOR: Direct access to SKU
    public function getSkuAttribute()
    {
        return $this->supplierProduct->system_sku ??
            $this->supplierProduct->supplier_sku ??
            'No SKU';
    }
}
