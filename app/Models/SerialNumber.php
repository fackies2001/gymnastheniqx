<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;

class SerialNumber extends Model implements Auditable
{
    //
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $table = 'serial_number';

    public $timestamps = true; // this is actually the default

    protected $fillable = [
        'sku_id',
        'serial_number',
        'purchase_order_id',
        'product_status_id',
        'warehouse_id',
        'scanned_by',
    ];
    protected $casts = [
        'images' => 'array',
    ];


    public function supplierProducts()
    {
        return $this->belongsTo(SupplierProduct::class, 'sku_id');
    }

    public function productStatus()
    {
        return $this->belongsTo(ProductStatus::class, 'product_status_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    // app/Models/SerialNumbers.php

    public function scannedBy()
    {
        // Palitan ang 'user_id' ng 'scanned_by'
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public static function monthlyProductScannedIn()
    {
        return self::selectRaw('COUNT(id) as total, MONTH(created_at) as month')
            ->groupByRaw('MONTH(created_at)');
    }

    public static function countsPerStatus()
    {
        return self::with('productStatus')
            // FIX: Siguraduhin na 'serial_number' (singular) ang table name dito
            ->join('product_status as ps', 'serial_number.product_status_id', '=', 'ps.id')
            ->selectRaw('ps.name as product_status_name, COUNT(serial_number.id) as total')
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
                DB::raw('COUNT(serial_number.id) as quantity')
            )
            ->join('supplier_product as sp', 'serial_number.sku_id', '=', 'sp.id')
            ->join('supplier as s', 'sp.supplier_id', '=', 's.id')
            ->join('product_status as ps', 'serial_number.product_status_id', '=', 'ps.id')
            ->join('purchase_order as po', 'serial_number.purchase_order_id', '=', 'po.id')
            ->join('employee as e', 'serial_number.scanned_by', '=', 'e.id')
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
        // if (!auth()->check()) return $query;
        // 'scannedBy' lang dapat, wag nang 'scannedBy.user'
        return $query;
    }


    public function scopeFilterByWarehouse($query)
    {
        if (!auth()->check()) return $query;
        // Gamitin ang department_id ng logged-in user
        $deptId = auth()->user()->department_id;
        return $query->whereRelation('scannedBy', 'department_id', $deptId);
    }
}
