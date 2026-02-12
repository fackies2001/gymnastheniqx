<?php
/*
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;

class SerialNumbers extends Model implements Auditable
{
    //
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $table = 'serial_numbers';

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
        return $this->belongsTo(SupplierProducts::class, 'sku_id');
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
        return $this->belongsTo(Warehouses::class, 'warehouse_id');
    }

    // app/Models/SerialNumbers.php

    public function scannedBy()
    {
        // 'scanned_by' ang foreign key sa serial_numbers table mo
        return $this->belongsTo(Employees::class, 'scanned_by');
    }

    public static function monthlyProductScannedIn()
    {
        return self::selectRaw('COUNT(id) as total, MONTH(created_at) as month')
            ->groupByRaw('MONTH(created_at)');
    }

    public static function countsPerStatus()
    {
        return self::with('productStatus')
            ->join('product_status as ps', 'serial_numbers.product_status_id', '=', 'ps.id')
            ->selectRaw('ps.name as product_status_name, COUNT(serial_numbers.id) as total')
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
                DB::raw('COUNT(serial_numbers.id) as quantity')
            )
            ->join('supplier_products as sp', 'serial_numbers.sku_id', '=', 'sp.id')
            ->join('suppliers as s', 'sp.supplier_id', '=', 's.id')
            ->join('product_status as ps', 'serial_numbers.product_status_id', '=', 'ps.id')
            ->join('purchase_orders as po', 'serial_numbers.purchase_order_id', '=', 'po.id')
            ->join('employees as e', 'serial_numbers.scanned_by', '=', 'e.id')
            ->leftJoin('users as u', 'e.id', '=', 'u.employee_id')
            ->join('categories as c', 'sp.category_id', '=', 'c.id')
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
        // Sinisiguro nito na hindi mag-eerror kung hindi naka-login ang user
        if (!auth()->check()) return $query;

        // Kinukuha ang ugnayan mula SerialNumbers -> scannedBy (Employee) -> user
        return $query->whereRelation('scannedBy.user', 'is_student', auth()->user()->is_student);
    }
    public function scopeFilterByWarehouse($query)
    {
        // Gamitan natin ng ?-> para hindi mag-crash kung null ang employee
        $assignedAt = auth()->user()->employee?->assigned_at;

        return $query->whereRelation('scannedBy', 'assigned_at', $assignedAt);
    }
}
