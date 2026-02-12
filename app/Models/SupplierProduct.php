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
    ];

    protected $casts = [
        'dimensions' => 'array',
        'images' => 'array',
        'barcode' => 'string'
    ];

    /* ========================================
       🔥 ADDED: CRITICAL SCOPES FOR FILTERING
       ======================================== */

    /**
     * Filter by student source
     * source_id = 2 for students, [1,3] for non-students
     */
    public function scopeFilterByStudent($query)
    {
        if (auth()->check()) {
            $isStudent = auth()->user()->is_student ?? false;
            $sourceIds = $isStudent ? [2] : [1, 3];
            return $query->whereIn('source_id', $sourceIds);
        }
        return $query;
    }

    /**
     * Filter by warehouse (if applicable)
     * Note: SupplierProduct doesn't have warehouse_id directly,
     * so we filter through serializedProducts relationship
     */
    public function scopeFilterByWarehouse($query)
    {
        // Check session first
        if (session()->has('warehouse_id')) {
            $warehouseId = session('warehouse_id');
            return $query->whereHas('serializedProducts', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        // Check user's warehouse
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
       ✅ RELATIONSHIPS
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

    // ✅ KEEP OLD RELATIONSHIP (for backward compatibility)
    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class, 'sku_id', 'id');
    }

    // ✅ NEW RELATIONSHIP (for new serialized_product table)
    // 🔥 THIS IS WHAT WE USE FOR THE DASHBOARD LOW STOCK ALERT
    public function serializedProducts()
    {
        return $this->hasMany(SerializedProduct::class, 'product_id');
    }

    public function source()
    {
        return $this->belongsTo(Source::class, 'source_id');
    }

    /* ========================================
       🔥 HELPER METHODS FOR DASHBOARD
       ======================================== */

    /**
     * Get available stock count (status = 1 means Available)
     * This counts serialized products that are available in warehouse
     */
    public function getAvailableStockAttribute()
    {
        return $this->serializedProducts()
            ->where('status', 1) // 1 = Available
            ->count();
    }

    /**
     * Check if product is low stock (below 20 units)
     */
    public function getIsLowStockAttribute()
    {
        return $this->available_stock < 20 && $this->available_stock > 0;
    }

    /* ========================================
       ✅ BOOT METHOD
       ======================================== */

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            // 1. Hanapin ang Category Name para makuha ang Abbreviation
            $category = \App\Models\Category::find($product->category_id);
            $abbrv = $category ? strtoupper(substr($category->name, 0, 3)) : 'PROD';

            // 2. Tawagin ang SkuHelper para i-generate ang system_sku
            $product->system_sku = \App\Helpers\SkuHelper::generateSystemSku($abbrv);

            // 3. Optional: Gawin na ring parehas ang supplier_sku kung empty
            if (empty($product->supplier_sku)) {
                $product->supplier_sku = $product->system_sku;
            }
        });
    }
}
