<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStatus extends Model
{
    use HasFactory;

    protected $table = 'product_status';

    protected $fillable = ['name'];

    public $timestamps = true;

    public function serializedProducts()
    {
        return $this->hasMany(SerializedProduct::class, 'product_status_id');
    }
}
