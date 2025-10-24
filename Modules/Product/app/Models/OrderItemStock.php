<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'orderitem_id',
        'stock_id',
        'quantity',
        'purchase_price',
        'sell_price',
        'total_price',
        'discount_amount',
        'actual_profit',
        'return_quantity',
        'damage_quantity',
        'lost_quantity'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'actual_profit' => 'decimal:2',
        'return_quantity' => 'integer',
        'damage_quantity' => 'integer',
        'lost_quantity' => 'integer',
    ];

    /**
     * Relationship with OrderItem
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'orderitem_id');
    }

    /**
     * Relationship with Stock
     */
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Boot method to calculate actual profit
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($orderItemStock) {
            $orderItemStock->actual_profit = ($orderItemStock->sell_price - $orderItemStock->purchase_price) * 
                                           ($orderItemStock->quantity - $orderItemStock->return_quantity - 
                                            $orderItemStock->damage_quantity - $orderItemStock->lost_quantity);
        });
    }

    /**
     * Get net quantity (quantity - returns - damage - lost)
     */
    public function getNetQuantityAttribute()
    {
        return $this->quantity - $this->return_quantity - $this->damage_quantity - $this->lost_quantity;
    }

    /**
     * Get profit per unit
     */
    public function getProfitPerUnitAttribute()
    {
        return $this->sell_price - $this->purchase_price;
    }
}