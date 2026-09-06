<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetailerOrder extends Model
{
    protected $table = 'retailer_orders';

    protected $fillable = [
        'product_id',             
        'retailer_name',
        'sku',
        'product_name',
        'quantity',
        'unit_price',               
        'total_amount',
        'status',
        'created_by',
        'user_role',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'allocated_serial_numbers', 
        'shipped_by',
        'shipped_at',
        'received_at',
        'created_by_user_id',
        'product_condition', 
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     *  Direct product relationship via product_id
     * Eliminates SKU mismatch issues
     */
    public function product()
    {
        return $this->belongsTo(SupplierProduct::class, 'product_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'Rejected');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }

    public function creatorUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
