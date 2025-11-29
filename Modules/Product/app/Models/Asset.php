<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'type',
        'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'type' => 'integer',
        'status' => 'boolean'
    ];

    /**
     * Scope for active assets
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
     * Get type text
     */
    public function getTypeTextAttribute()
    {
        switch ($this->type) {
            case 1:
                return 'Investment';
            case 2:
                return 'Profit';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get type badge class
     */
    public function getTypeBadgeClassAttribute()
    {
        switch ($this->type) {
            case 1:
                return 'bg-primary'; // Investment
            case 2:
                return 'bg-success'; // Profit
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Scope for investment type
     */
    public function scopeInvestment($query)
    {
        return $query->where('type', 1);
    }

    /**
     * Scope for profit type
     */
    public function scopeProfit($query)
    {
        return $query->where('type', 2);
    }
}
