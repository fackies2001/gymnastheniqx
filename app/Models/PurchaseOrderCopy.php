<?php
/*
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    // ✅ TAMA: Singular table name based sa database mo
    protected $table = 'purchase_order';

    // ✅ FIXED: Match EXACTLY sa database columns from HeidiSQL
    protected $fillable = [
        'po_number',
        'purchase_request_id',  // ✅ TAMA: Based sa DB screenshot
        'supplier_id',
        'approved_by',
        'requested_by',
        'order_date',
        'delivery_date',
        'payment_terms',        // ✅ TAMA: ENUM('cash_on_delivery','bank_transfer')
        'remarks',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
    ];

    // Scopes
    public function scopeFilterByStudent($query)
    {
        if (auth()->check()) {
            // Uncomment if you want to filter by user
            // return $query->where('requested_by', auth()->id());
        }
        return $query;
    }

    public function scopeFilterByWarehouse($query)
    {
        if (session()->has('warehouse_id')) {
            return $query->where('warehouse_id', session('warehouse_id'));
        }

        if (auth()->check() && auth()->user()->warehouse_id) {
            return $query->where('warehouse_id', auth()->user()->warehouse_id);
        }

        return $query;
    }

    public function scopeFilterByStatus($query, $statusFilter)
    {
        return $query->whereIn('status', $statusFilter);
    }

    public function scopeWithExclusion($query, $statusExcluded)
    {
        return $query->whereNotIn('status', $statusExcluded);
    }

    // Relationships
    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function serializedProducts(): HasMany
    {
        return $this->hasMany(SerializedProduct::class);
    }
}

24-01-26