<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 */
class Category extends Model
{
    use HasFactory;

    protected $table = 'category';
    
    protected $fillable = [
        'name',
        'description',
    ];
    public $timestamps = true; // this is actually the default
    public function supplierProduct()
    {
        return $this->hasMany(SupplierProduct::class, 'category_id');
    }
}
