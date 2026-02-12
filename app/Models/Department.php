<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'department';

    protected $fillable = ['name', 'description'];

    // Employees in this department
    public function employee()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }
}
