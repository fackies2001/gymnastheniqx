<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employee';

    protected $fillable = [
        'full_name',
        'email',        
        'username',
        'password',     
        'role_id',
        'department_id', 
        'contact_number',
        'address',
        'date_hired',
        'profile_photo',
        'status',
        'assigned_at',
        'pin',
        'user_id',
        'first_name',
        'last_name',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'assigned_at');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
