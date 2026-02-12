<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coach extends Model
{
    use HasFactory;

    protected $table = 'coaches'; // Pangalan ng table sa migration

    protected $fillable = [
        'full_name',
        'birth_date',
        'address',
        'contact_no',
        'email',
        'position',
        'date_hired',
        'status',
    ];
}
