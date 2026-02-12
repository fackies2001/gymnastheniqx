<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTerm extends Model
{
    use HasFactory;
    protected $table = 'payment_term';

    protected $fillable = [
        'name',
        'description',
        'days',
        'discount_rate',
        'discount_days',
    ];


    public function purchase_order()
    {
        return $this->hasMany(PurchaseOrder::class, 'payment_term_id');
    }
}
