<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    // ✅ TAMA: Singular table name based sa database mo
    protected $table = 'purchase_order';

    // ✅ FIXED: Added status, grand_total, and warehouse_id columns
    protected $fillable = [
        'po_number',
        'purchase_request_id',
        'supplier_id',
        'approved_by',
        'requested_by',
        'order_date',
        'delivery_date',
        'payment_terms',
        'remarks',
        'status',               // ✅ Para sa status tracking
        'grand_total',          // ✅ Para sa total amount
        'warehouse_id',         // ✅ ADDED: Para sa warehouse filtering
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'grand_total' => 'decimal:2',
    ];

    /* ========================================
       ✅ SCOPES FOR FILTERING
       ======================================== */

    /**
     * Filter by student (if applicable)
     */
    public function scopeFilterByStudent($query)
    {
        if (auth()->check()) {
            $isStudent = auth()->user()->is_student ?? false;

            // Students might have different filtering logic
            // Uncomment if you want to filter by user
            // if ($isStudent) {
            //     return $query->where('requested_by', auth()->id());
            // }
        }

        return $query;
    }

    /**
     * Filter by warehouse
     * This filters purchase orders by warehouse
     */
    public function scopeFilterByWarehouse($query)
    {
        // Check session first (highest priority)
        if (session()->has('warehouse_id')) {
            return $query->where('warehouse_id', session('warehouse_id'));
        }

        // Check user's warehouse
        if (auth()->check() && auth()->user()->warehouse_id) {
            return $query->where('warehouse_id', auth()->user()->warehouse_id);
        }

        return $query;
    }

    /**
     * Filter by status
     */
    public function scopeFilterByStatus($query, $statusFilter)
    {
        return $query->whereIn('status', $statusFilter);
    }

    /**
     * Exclude certain statuses
     */
    public function scopeWithExclusion($query, $statusExcluded)
    {
        return $query->whereNotIn('status', $statusExcluded);
    }

    /* ========================================
       ✅ RELATIONSHIPS
       ======================================== */

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

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * ✅ ADDED: Payment terms relationship
     */
    public function paymentTerms(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_terms');
    }
}
