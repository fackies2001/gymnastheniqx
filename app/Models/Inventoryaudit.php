<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAudit extends Model
{
    protected $table = 'inventory_audits';

    protected $fillable = [
        'product_name',
        'product_sku',
        'system_count',
        'actual_count',
        'variance',
        'status',
        'audit_period',
        'audited_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
