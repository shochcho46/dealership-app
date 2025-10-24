<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamageReturnLost extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'stock_id',
        'order_id',
        'quantity',
        'status',
        'purchase_price',
        'total_price'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'status' => 'integer',
        'purchase_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Relationship with Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relationship with Stock
     */
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Relationship with Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case 1:
                return 'Damage';
            case 2:
                return 'Lost';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 1:
                return 'bg-warning'; // Damage
            case 2:
                return 'bg-danger'; // Lost
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Scope for damage records
     */
    public function scopeDamage($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for lost records
     */
    public function scopeLost($query)
    {
        return $query->where('status', 2);
    }
}