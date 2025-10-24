<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'mobile',
        'contact_person',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Scope for active warehouses
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    /**
     * Relationship with stocks
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}