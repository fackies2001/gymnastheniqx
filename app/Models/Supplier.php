<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Supplier extends Model implements Auditable
{
    /** @use HasFactory<\Database\Factories\SupplierFactory> */
    use HasFactory, \OwenIt\Auditing\Auditable;

    public $timestamps = true; // this is actually the default

    protected $table = 'supplier';

    protected $fillable = [
        'name',
        'contact_person', // Idagdag ito
        'contact_number', // Idagdag ito kapalit ng 'phone'
        'email',
        'address',
        'created_by',
        'source_id'
    ];

    public function supplierBarcodes()
    {
        return $this->hasMany(SupplierBarcodes::class, 'supplier_id');
    }

    public function supplierProducts()
    {
        return $this->hasMany(SupplierProduct::class, 'supplier_id');
    }

    public function supplierApis()
    {
        return $this->hasOne(SupplierApis::class, 'supplier_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function source()
    {
        return $this->belongsTo(Source::class, 'source_id');
    }

    public function scopeWithId($query, $id)
    {
        return $query->where('id', $id);
    }
    public function scopeFilterBySource($query, $source_id)
    {
        return $query->whereIn('source_id', $source_id);
    }


    public function scopeFilterByStudent($query)
    {
        return $query->whereRelation('createdBy.user', 'is_student', auth()->user()->is_student);
    }
}
