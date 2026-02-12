<?php
/*
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

// class SupplierProduct extends Model implements Auditable
// {
//     use HasFactory, \OwenIt\Auditing\Auditable;

//     protected $table = 'supplier_product';

//     public $timestamps = true;

//     protected $fillable = [
//         'supplier_id',
//         'category_id',
//         'name',
//         'is_consumable',
//         'supplier_sku',
//         'system_sku',
//         'cost_price',
//         'discount',
//         'availability_status',
//         'shipping_information',
//         'warranty_information',
//         'return_policy',
//         'dimensions',
//         'barcode',
//         'thumbnail',
//         'images',
//         'source_id',
//     ];

//     protected $casts = [
//         'dimensions' => 'array',
//         'images' => 'array',
//         'barcode' => 'string'
//     ];

//     public function scopeWithSupplierId($query, $supplier_id)
//     {
//         return $query->where('supplier_id', $supplier_id);
//     }

//     public function category()
//     {
//         return $this->belongsTo(Category::class, 'category_id');
//     }

//     public function supplier()
//     {
//         return $this->belongsTo(Supplier::class, 'supplier_id');
//     }

//     public function purchaseRequest()
//     {
//         return $this->hasMany(PurchaseRequest::class, 'supplier_product_id');
//     }

//     // ✅ KEEP OLD RELATIONSHIP (for backward compatibility)
//     public function serialNumbers()
//     {
//         return $this->hasMany(SerialNumber::class, 'sku_id', 'id');
//     }

//     // ✅ NEW RELATIONSHIP (for new serialized_product table)
//     public function serializedProducts()
//     {
//         return $this->hasMany(SerializedProduct::class, 'product_id');
//     }

//     public function source()
//     {
//         return $this->belongsTo(Source::class, 'source_id');
//     }

//     public function scopeFilterBySource($query, $source_id)
//     {
//         return $query->whereIn('source_id', $source_id);
//     }

//     protected static function boot()
//     {
//         parent::boot();

//         static::creating(function ($product) {
//             // 1. Hanapin ang Category Name para makuha ang Abbreviation
//             $category = \App\Models\Category::find($product->category_id);
//             $abbrv = $category ? strtoupper(substr($category->name, 0, 3)) : 'PROD';

//             // 2. Tawagin ang SkuHelper para i-generate ang system_sku
//             $product->system_sku = \App\Helpers\SkuHelper::generateSystemSku($abbrv);

//             // 3. Optional: Gawin na ring parehas ang supplier_sku kung empty
//             if (empty($product->supplier_sku)) {
//                 $product->supplier_sku = $product->system_sku;
//             }
//         });
//     }
// }

27-01-2026