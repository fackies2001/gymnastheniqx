<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsumableStock extends Model
{
    protected $table = 'consumable_stocks';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'current_qty',
        'min_stock_level',
    ];

    // ─── RELATIONSHIPS ───────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(SupplierProduct::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    // ─── SCOPES ──────────────────────────────────────────────

    // ✅ Low stock items: current_qty <= min_stock_level
    public function scopeLowStock($query)
    {
        // ✅ Strictly LESS THAN — hindi lalabas kung exactly equal sa min
        return $query->whereColumn('current_qty', '<', 'min_stock_level');
    }

    // ✅ Filter by warehouse
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    // ─── HELPERS ─────────────────────────────────────────────

    public function isLowStock(): bool
    {
        return $this->current_qty < $this->min_stock_level;
    }

    /**
     * ✅ ADDED: Phase 3 Stock Health
     */
    public function getStockStatus()
    {
        $qty = $this->current_qty;
        
        if ($qty <= 5) {
            return (object)[
                'label' => 'Critical',
                'color' => 'danger',
                'icon'  => 'exclamation-circle'
            ];
        } elseif ($qty <= 15) {
            return (object)[
                'label' => 'Low Stock',
                'color' => 'orange',
                'icon'  => 'exclamation-triangle'
            ];
        } elseif ($qty <= 25) {
            return (object)[
                'label' => 'Warning',
                'color' => 'warning',
                'icon'  => 'clock'
            ];
        } else {
            return (object)[
                'label' => 'Healthy',
                'color' => 'success',
                'icon'  => 'check-circle'
            ];
        }
    }
}
