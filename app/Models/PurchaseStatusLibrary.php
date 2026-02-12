<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseStatusLibrary extends Model
{
    protected $table = 'purchase_status_library';
    protected $fillable = ['code', 'name', 'description', 'color'];

    public function requests()
    {
        return $this->hasMany(PurchaseRequest::class, 'status_id');
    }
}
