<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\PurchaseRequest;

class Source extends Model
{
    public $timestamps = true;

    protected $table = 'source'; 

    protected $fillable = ['name', 'description']; 

    public function supplier()
    {
        return $this->hasMany(Supplier::class, 'source_id'); 
    }

    public function supplierProducts()
    {
        return $this->hasMany(SupplierProduct::class, 'source_id'); 
    }

    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class, 'source_id'); 
    }
}
