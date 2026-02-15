<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequest extends Model
{

    protected $table = 'purchase_request';

    protected $fillable = [
        'request_number',
        'user_id',
        'department_id',
        'supplier_id',
        'status_id',
        'order_date',
        'estimated_delivery_date',
        'payment_term_id',
        'remarks',
        'approved_by',
        'approved_at',
        'warehouse_id',
    ];

    protected $casts = [
        'item' => 'array',
        'order_date' => 'date',
        'estimated_delivery_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Scopes



    public function scopeFilterByStudent($query)
    {
        if (auth()->check()) {
            // Uncomment if you want to filter by user
            // return $query->where('user_id', auth()->id());
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

    public function scopeWithRequestNumber($query, $requestNumber)
    {
        return $query->where('request_number', $requestNumber);
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function requestedBy(): BelongsTo  // ✅ Added
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    // ✅ ADD THIS RELATIONSHIP
    public function status(): BelongsTo
    {
        return $this->belongsTo(PurchaseStatusLibrary::class, 'status_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestItem::class, 'purchase_request_id');
    }

    public function purchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, 'purchase_request_id'); // ✅ TAMA
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
