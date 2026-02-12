<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model

{
    protected $table = 'sale';

    protected $fillable = ['product_id', 'quantity', 'total_price', 'created_at'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
