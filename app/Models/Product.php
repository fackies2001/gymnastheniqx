<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';

    protected $fillable = [
        'product_name',
        'category',
        'quantity',
        'price',
        'status',
        'serial_number',
        'po_id',
        'received_by',
        'warehouse_id'
    ];

    public function category_info()
    {
        return $this->belongsTo(Category::class, 'category');
    }

    public function purchase_order()
    {
        // TAMA: Tumuturo sa PurchaseOrders model mo
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function receiver()
    {
        // ADJUSTMENT: Palitan ng Employees::class kung doon naka-save ang staff details
        // base sa PurchaseRequests model mo kanina
        return $this->belongsTo(Employee::class, 'received_by');
    }

    public function warehouse_info()
    {
        // Siguraduhin na ang table name mo ay 'warehouses' o 'branches'
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function needs_purchase_request()
    {
        $pendingDemand = RetailerOrder::where('status', 'Pending')->sum('quantity');
        return $this->quantity < $pendingDemand;
    }
}
