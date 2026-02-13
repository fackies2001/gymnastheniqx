<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\PurchaseRequest;

class Source extends Model
{
    public $timestamps = true;

    protected $table = 'source'; // ✅ Add this for clarity

    protected $fillable = ['name', 'description']; // ✅ Add fillable

    public function supplier()
    {
        return $this->hasMany(Supplier::class, 'source_id'); // ✅ ADD return
    }

    public function supplierProducts()
    {
        return $this->hasMany(SupplierProduct::class, 'source_id'); // ✅ ADD return
    }

    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class, 'source_id'); // ✅ ADD return
    }
}
