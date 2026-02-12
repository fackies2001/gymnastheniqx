<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierBarcodes extends Model
{
    //
    use HasFactory;

    protected $table = 'supplier_barcodes';

    public $timestamps = true; // this is actually the default

    public function suppliers()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function supplier_products()
    {
        return $this->belongsTo(SupplierProduct::class, 'sku_id');
    }
}
