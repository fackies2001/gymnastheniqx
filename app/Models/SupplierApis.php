<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierApis extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierFactory> */
    use HasFactory;

    public $timestamps = true; // this is actually the default

    protected $table = 'supplier_apis';
    protected $fillable = [
        'supplier_id',
        'api_url',
        'headers',
        'service_class',
    ];
    public function supplierBarcodes()
    {
        return $this->hasMany(SupplierBarcodes::class, 'supplier_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Suppliers::class, 'supplier_id');
    }
}
