<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    // Tiyaking 'warehouses' ang table name
    protected $table = 'warehouse';

    // Kumpletong fields para sa real data submission
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'assignee',
        'location',
        'description',
        'is_active'
    ];
}
