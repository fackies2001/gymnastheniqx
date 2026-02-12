<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retailer extends Model
{
    use HasFactory;

    // Kung ang table name mo ay hindi "retailers", i-specify mo rito:
    // protected $table = 'retailers';

    protected $fillable = ['name', 'address', 'contact_number'];

    // Relationship sa orders (kung kailangan mo)
    public function orders()
    {
        return $this->hasMany(RetailerOrder::class);
    }
}
