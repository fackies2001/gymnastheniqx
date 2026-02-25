<?php

namespace App\Models;

use App\Models\Department;
use App\Models\Role;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function getNameAttribute()
    {
        return $this->full_name ?? 'Unknown User';
    }

    public function employee(): HasOne
    {
        return $this->hasOne(self::class, 'id', 'id');
    }

    public function getEmployeeIdAttribute()
    {
        return $this->id;
    }

    public function adminlte_image()
    {
        if ($this->profile_photo) {
            $fullPath = storage_path('app/public/' . $this->profile_photo);
            if (file_exists($fullPath)) {
                return asset('storage/' . $this->profile_photo);
            }
        }

        $nameParts = explode(' ', trim($this->full_name ?? 'User'));
        $name = implode(' ', array_slice($nameParts, 0, 2));
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=6777ef&color=fff&size=128';
    }

    public function adminlte_desc()
    {
        return 'Member since ' . $this->created_at->format('M Y');
    }

    public function adminlte_role()
    {
        return $this->role?->role_name ?? 'No Role';
    }

    public function adminlte_warehouse()
    {
        return $this->warehouse?->name ?? 'No Warehouse';
    }

    public function adminlte_profile_url()
    {
        return route('profile.edit');
    }

    // ✅ NEW ROLE HELPER METHODS
    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role?->role_name === 'admin';
    }

    /**
     * Check if user is manager
     */
    public function isManager()
    {
        return $this->role?->role_name === 'manager';
    }

    /**
     * Check if user is staff
     */
    public function isStaff()
    {
        return $this->role?->role_name === 'staff';
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role)
    {
        return $this->role?->role_name === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles)
    {
        return in_array($this->role?->role_name, $roles);
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
