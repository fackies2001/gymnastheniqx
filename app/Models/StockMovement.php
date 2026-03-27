<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockMovement extends Model
{
    protected $table = 'stock_movements';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'type',
        'quantity',
        'reason_type',
        'remarks',
        'purchase_order_id',
        'retailer_order_id',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ─── CONSTANTS ───────────────────────────────────────────

    // Movement types
    const TYPE_IN         = 'in';
    const TYPE_OUT        = 'out';
    const TYPE_DAMAGE     = 'damage';
    const TYPE_LOSS       = 'loss';
    const TYPE_ADJUSTMENT = 'adjustment';

    // Reason types
    const REASON_DOA              = 'defective_on_arrival';
    const REASON_DAMAGED_STORAGE  = 'damaged_in_storage';
    const REASON_LEAKED           = 'leaked';
    const REASON_EXPIRED          = 'expired';
    const REASON_LOST_TRANSIT     = 'lost_in_transit';
    const REASON_MISSING_COUNT    = 'missing_in_count';
    const REASON_SOLD             = 'sold_to_retailer';
    const REASON_RECEIVED         = 'received_from_supplier';
    const REASON_CORRECTION       = 'stock_correction';
    const REASON_OTHER            = 'other';

    // ─── RELATIONSHIPS ───────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(SupplierProduct::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function retailerOrder()
    {
        return $this->belongsTo(RetailerOrder::class, 'retailer_order_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── SCOPES ──────────────────────────────────────────────

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }

    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Daily inflow (IN movements today)
    public function scopeDailyReceived($query)
    {
        return $query->today()->where('type', self::TYPE_IN);
    }

    // Daily outflow (OUT movements today — ibinenta sa retailer)
    public function scopeDailyOutflow($query)
    {
        return $query->today()->where('type', self::TYPE_OUT);
    }

    // Daily damaged/lost
    public function scopeDailyDamagedLost($query)
    {
        return $query->today()->whereIn('type', [self::TYPE_DAMAGE, self::TYPE_LOSS]);
    }

    // ─── STATIC HELPERS ──────────────────────────────────────

    /**
     * ✅ Record a stock movement AND update the consumable_stocks table
     * Gamitin ito sa lahat ng stock changes — hindi direct insert sa DB
     *
     * @param array $data
     * @return StockMovement
     */
    public static function record(array $data): self
    {
        return DB::transaction(function () use ($data) {
            // 1. Save the movement log
            $movement = self::create($data);

            // 2. Update the current stock in consumable_stocks
            $stock = ConsumableStock::firstOrCreate(
                [
                    'product_id'   => $data['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                ],
                ['current_qty' => 0, 'min_stock_level' => 20]
            );

            // IN and positive ADJUSTMENT = dagdag
            // OUT, DAMAGE, LOSS, negative ADJUSTMENT = bawas
            if (in_array($data['type'], [self::TYPE_IN, self::TYPE_ADJUSTMENT])) {
                $qty = $data['type'] === self::TYPE_ADJUSTMENT
                    ? $data['quantity']   // can be negative for correction
                    : abs($data['quantity']);
                $stock->increment('current_qty', $qty);
            } else {
                $stock->decrement('current_qty', abs($data['quantity']));
            }

            return $movement;
        });
    }

    /**
     * ✅ Daily summary for dashboard cards
     * Returns: received, outflow, damaged_lost counts for today
     */
    public static function dailySummary($warehouseId = null): array
    {
        $base = self::today();

        if ($warehouseId) {
            $base = $base->forWarehouse($warehouseId);
        }

        return [
            'daily_received'     => (clone $base)->where('type', self::TYPE_IN)->sum('quantity'),
            'daily_outflow'      => (clone $base)->where('type', self::TYPE_OUT)->sum('quantity'),
            'daily_damaged_lost' => (clone $base)->whereIn('type', [self::TYPE_DAMAGE, self::TYPE_LOSS])->sum('quantity'),
        ];
    }
}
