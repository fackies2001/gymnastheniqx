<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\PurchaseRequest;

class Source extends Model
{
    //
    public $timestamps = true;

    public function supplier()
    {
        $this->hasMany(Supplier::class, 'source_id');
    }

    public function supplierProducts()
    {
        $this->hasMany(SupplierProduct::class, 'source_id');
    }

    public function purchaseRequests()
    {
        $this->hasMany(PurchaseRequest::class, 'source_id');
    }
}
