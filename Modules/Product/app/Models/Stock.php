<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Product\Database\factories\StockFactory;
use Modules\Product\Models\Product;

class Stock extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_id',
        'purchase_price',
        'quantity',
        'total_price',
        'sell_price',
        'damage_quantity',
        'sold_quantity',
        'stolen_quantity',
        'transfer_quantity',
        'froze_quantity',
        'status',
        'manufacture_date',
        'expire_date'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'purchase_price' => 'decimal:2',
        'quantity' => 'integer',
        'total_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'damage_quantity' => 'integer',
        'sold_quantity' => 'integer',
        'stolen_quantity' => 'integer',
        'transfer_quantity' => 'integer',
        'froze_quantity' => 'integer',
        'status' => 'boolean',
        'manufacture_date' => 'date',
        'expire_date' => 'date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [];

    /**
     * Get the product that owns the stock.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse that owns the stock.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get order items that use this stock
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope a query to only include active stocks.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include stocks with available quantity
     */
    public function scopeAvailable($query)
    {
        return $query->whereRaw('quantity > (sold_quantity + damage_quantity + stolen_quantity + froze_quantity)');
    }

    /**
     * Get remaining quantity (total - sold - damaged - stolen - frozen)
     */
    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->sold_quantity - $this->damage_quantity - $this->stolen_quantity - $this->froze_quantity;
    }

    /**
     * Get available quantity for new orders
     */
    public function getAvailableQuantityAttribute()
    {
        return $this->remaining_quantity;
    }

    /**
     * Check if stock has sufficient quantity available
     */
    public function hasSufficientQuantity($requestedQuantity)
    {
        return $this->available_quantity >= $requestedQuantity;
    }

    /**
     * Allocate quantity for order (increase froze_quantity)
     */
    public function allocateQuantity($quantity)
    {
        if (!$this->hasSufficientQuantity($quantity)) {
            throw new \Exception("Insufficient quantity available. Available: {$this->available_quantity}, Requested: {$quantity}");
        }

        $this->increment('froze_quantity', $quantity);
        return true;
    }

    /**
     * Release allocated quantity (decrease froze_quantity)
     */
    public function releaseQuantity($quantity)
    {
        $this->froze_quantity = max(0, $this->froze_quantity - $quantity);
        $this->save();
        return true;
    }

    /**
     * Convert frozen quantity to sold quantity (when order is completed)
     */
    public function convertFrozeToSold($quantity)
    {
        if ($this->froze_quantity < $quantity) {
            throw new \Exception("Cannot convert more than frozen quantity. Frozen: {$this->froze_quantity}, Requested: {$quantity}");
        }

        $this->froze_quantity -= $quantity;
        $this->sold_quantity += $quantity;
        $this->save();
        return true;
    }

    /**
     * Get stock status based on remaining quantity
     */
    public function getStockStatusAttribute()
    {
        $remaining = $this->remaining_quantity;
        $percentage = $this->quantity > 0 ? ($remaining / $this->quantity) * 100 : 0;

        if ($remaining <= 0) {
            return 'Out of Stock';
        } elseif ($percentage <= 10) {
            return 'Critical';
        } elseif ($percentage <= 25) {
            return 'Low';
        } else {
            return 'In Stock';
        }
    }

    /**
     * Get stock status badge class
     */
    public function getStockStatusBadgeClassAttribute()
    {
        switch ($this->stock_status) {
            case 'Out of Stock':
                return 'bg-danger';
            case 'Critical':
                return 'bg-warning';
            case 'Low':
                return 'bg-info';
            case 'In Stock':
                return 'bg-success';
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Generate batch ID based on current date and timestamp
     */
    public static function generateBatchId()
    {
        return 'B-' . now()->format('YmdHis');
    }




}
