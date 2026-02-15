<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;

class SerialNumber extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $table = 'serialized_product';

    public $timestamps = true;

    protected $fillable = [
        'product_id',
        'purchase_order_id',
        'barcode',
        'serial_number',
        'status',
        'scanned_by',
        'scanned_at',
        'warehouse_id',
        'remarks'
    ];

    protected $casts = [
        'images' => 'array',
        'scanned_at' => 'datetime',
    ];

    // ✅ FIXED: scannedBy points to User (which is employee table)
    public function scannedBy()
    {
        return $this->belongsTo(User::class, 'scanned_by', 'id');
    }

    public function supplierProducts()
    {
        return $this->belongsTo(SupplierProduct::class, 'product_id', 'id');
    }

    public function productStatus()
    {
        return $this->belongsTo(ProductStatus::class, 'status', 'id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public static function monthlyProductScannedIn()
    {
        return self::selectRaw('COUNT(id) as total, MONTH(created_at) as month')
            ->groupByRaw('MONTH(created_at)');
    }

    public static function countsPerStatus()
    {
        return self::with('productStatus')
            ->join('product_status as ps', 'serialized_product.status', '=', 'ps.id')
            ->selectRaw('ps.name as product_status_name, COUNT(serialized_product.id) as total')
            ->groupBy('ps.name');
    }

    public function scopeSerializedProducts($query)
    {
        return $query
            ->select(
                'sp.id as supplier_product_id',
                'sp.name as product_name',
                'sp.system_sku as system_sku',
                'sp.images',
                'c.id as category_id',
                'c.name as category_name',
                's.id as supplier_id',
                's.name as supplier_name',
                DB::raw('COUNT(serialized_product.id) as quantity')
            )
            ->join('supplier_product as sp', 'serialized_product.product_id', '=', 'sp.id')
            ->join('supplier as s', 'sp.supplier_id', '=', 's.id')
            ->join('product_status as ps', 'serialized_product.status', '=', 'ps.id')
            ->join('purchase_order as po', 'serialized_product.purchase_order_id', '=', 'po.id')
            ->join('employee as e', 'serialized_product.scanned_by', '=', 'e.id')
            ->join('category as c', 'sp.category_id', '=', 'c.id')
            ->groupBy(
                'sp.id',
                'sp.name',
                'sp.system_sku',
                'sp.images',
                'c.id',
                'c.name',
                's.id',
                's.name'
            );
    }

    public function scopeFilterByStudent($query)
    {
        return $query;
    }

    public function scopeFilterByWarehouse($query)
    {
        if (!auth()->check()) return $query;
        $deptId = auth()->user()->department_id;
        return $query->whereRelation('scannedBy', 'department_id', $deptId);
    }
}
