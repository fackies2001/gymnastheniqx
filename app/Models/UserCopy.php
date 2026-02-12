<?php
/*
namespace App\Models;

use App\Models\Department;
use App\Models\Role;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_hired' => 'date',
        ];
    }

    // Laravel expects 'name' attribute for user display
    public function getNameAttribute()
    {
        return $this->full_name ?? 'Unknown User';
    }

    // AdminLTE profile image
    public function adminlte_image()
    {
        if ($this->profile_photo) {
            $fullPath = storage_path('app/public/' . $this->profile_photo);
            if (file_exists($fullPath)) {
                return asset('storage/' . $this->profile_photo);
            }
        }

        // Fallback to UI Avatars with first letter
        $initial = $this->full_name ? substr($this->full_name, 0, 1) : 'U';
        return 'https://ui-avatars.com/api/?name=' . urlencode($initial) . '&background=6777ef&color=fff&size=128';
    }

    // AdminLTE description (shows below name in dropdown)
    public function adminlte_desc()
    {
        return 'Member since ' . $this->created_at->format('M Y');
    }

    // AdminLTE role display (for sidebar)
    public function adminlte_role()
    {
        return $this->role?->role_name ?? 'No Role';
    }

    // AdminLTE warehouse display
    public function adminlte_warehouse()
    {
        return $this->warehouse?->name ?? 'No Warehouse';
    }

    // AdminLTE profile URL
    public function adminlte_profile_url()
    {
        return route('profile.edit');
    }

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'assigned_at');
    }
}

27-01-2026