<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GymEquipment extends Model
{
    use HasFactory;

    protected $table = 'gym_equipments'; // ✅ DAGDAG ITO!

    protected $fillable = [
        'name',
        'item_code',
        'quantity',
        'status',
    ];
}
