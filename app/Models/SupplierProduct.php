<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property int $supplier_id
 * @property int $category_id
 * @property string $name
 * @property string|null $supplier_sku
 * @property string $system_sku
 * @property float|null $cost_price
 * @property float|null $selling_price
 * @property int|null $stock
 * @property string|null $availability_status
 * @property string|null $shipping_information
 * @property string|null $warranty_information
 * @property string|null $return_policy
 * @property array|null $dimensions
 * @property string|null $barcode
 * @property string|null $thumbnail
 * @property array|null $images
 */

class SupplierProduct extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $table = 'supplier_product';

    public $timestamps = true;

    protected $fillable = [
        'supplier_id',
        'category_id',
        'name',
        'is_consumable',
        'supplier_sku',
        'system_sku',
        'cost_price',
        'selling_price', //  ADDED — presyo ng ibebenta sa retailer
        'discount',
        'availability_status',
        'shipping_information',
        'warranty_information',
        'return_policy',
        'dimensions',
        'barcode',
        'thumbnail',
        'images',
        'source_id',
        'pieces_per_box',
        'min_stock_level', // ✅ ADDED: Phase 3 Color-coded stock
    ];

    protected $casts = [
        'dimensions'    => 'array',
        'images'        => 'array',
        'barcode'       => 'string',
        'cost_price'    => 'decimal:2',
        'selling_price' => 'decimal:2',
        'pieces_per_box' => 'integer',
        'min_stock_level' => 'integer',
    ];

    /**
     * ✅ ADDED: Phase 3 - Generic 4-tier stock status
     */
    public function getStockStatus()
    {
        $qty = $this->available_stock;
        
        if ($qty <= 5) {
            return (object)[
                'label' => 'Critical',
                'color' => 'danger',
                'icon'  => 'exclamation-circle'
            ];
        } elseif ($qty <= 15) {
            return (object)[
                'label' => 'Low Stock',
                'color' => 'orange', // handled as warning in basic BS, custom in CSS
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

    /* ========================================
        SCOPES
       ======================================== */

    public function scopeFilterByStudent($query)
    {
        if (auth()->check()) {
            $isStudent = auth()->user()->is_student ?? false;
            $sourceIds = $isStudent ? [2] : [1, 3];
            return $query->whereIn('source_id', $sourceIds);
        }
        return $query;
    }

    public function scopeFilterByWarehouse($query)
    {
        if (session()->has('warehouse_id')) {
            $warehouseId = session('warehouse_id');
            return $query->whereHas('serializedProducts', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        if (auth()->check() && auth()->user()->warehouse_id) {
            $warehouseId = auth()->user()->warehouse_id;
            return $query->whereHas('serializedProducts', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        return $query;
    }

    public function scopeWithSupplierId($query, $supplier_id)
    {
        return $query->where('supplier_id', $supplier_id);
    }

    public function scopeFilterBySource($query, $source_id)
    {
        return $query->whereIn('source_id', $source_id);
    }

    /* ========================================
        RELATIONSHIPS
       ======================================== */

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchaseRequest()
    {
        return $this->hasMany(PurchaseRequest::class, 'supplier_product_id');
    }

    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class, 'sku_id', 'id');
    }

    public function serializedProducts()
    {
        return $this->hasMany(SerializedProduct::class, 'product_id');
    }

    public function consumableStocks()
    {
        return $this->hasOne(\App\Models\ConsumableStock::class, 'product_id');
    }

    public function source()
    {
        return $this->belongsTo(Source::class, 'source_id');
    }

    /* ========================================
        HELPER METHODS
       ======================================== */

    public function getAvailableStockAttribute()
    {
        // ✅ ALL products now use quantity-based tracking (ConsumableStock)
        return $this->consumableStocks()->sum('current_qty') ?? 0;
    }

    public function getIsLowStockAttribute()
    {
        return $this->available_stock < 20 && $this->available_stock > 0;
    }

    /**
     *  NEW: Get effective selling price
     * Kung walang selling_price, fallback sa cost_price
     * Para hindi maging 0 ang unit price sa orders
     */
    public function getEffectiveSellingPriceAttribute()
    {
        return $this->selling_price ?? $this->cost_price ?? 0;
    }

    /**
     *  NEW: Get markup amount
     */
    public function getMarkupAmountAttribute()
    {
        if (!$this->selling_price || !$this->cost_price) return 0;
        return $this->selling_price - $this->cost_price;
    }

    /**
     *  NEW: Get markup percentage
     */
    public function getMarkupPercentageAttribute()
    {
        if (!$this->cost_price || $this->cost_price == 0) return 0;
        return round((($this->selling_price - $this->cost_price) / $this->cost_price) * 100, 2);
    }

    /* ========================================
        BOOT METHOD
       ======================================== */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $category = \App\Models\Category::find($product->category_id);
            $abbrv    = $category ? strtoupper(substr($category->name, 0, 3)) : 'PROD';

            $product->system_sku = \App\Helpers\SkuHelper::generateSystemSku($abbrv);

            if (empty($product->supplier_sku)) {
                $product->supplier_sku = $product->system_sku;
            }
        });
    }
}
