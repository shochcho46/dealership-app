<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'purchase_price',
        'sell_price',
        'total_price',
        'discount_price',
        'return_quantity',
        'damage_quantity',
        'lost_quantity'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'return_quantity' => 'integer',
        'damage_quantity' => 'integer',
        'lost_quantity' => 'integer',
    ];

    /**
     * Relationship with Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship with Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relationship with OrderItemStocks
     */
    public function orderItemStocks()
    {
        return $this->hasMany(OrderItemStock::class, 'orderitem_id');
    }

    /**
     * Relationship with Stock
     */
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Get net price after discount
     */
    public function getNetPriceAttribute()
    {
        return ($this->sell_price * $this->quantity) - $this->discount_price;
    }

    /**
     * Get profit per item
     */
    public function getProfitPerItemAttribute()
    {
        return $this->sell_price - $this->purchase_price;
    }

    /**
     * Get total profit for this item
     */
    public function getTotalProfitAttribute()
    {
        return $this->profit_per_item * $this->quantity;
    }

    /**
     * Get effective quantity (total - returns - damage - lost)
     */
    public function getEffectiveQuantityAttribute()
    {
        return $this->quantity - $this->return_quantity - $this->damage_quantity - $this->lost_quantity;
    }

    /**
     * Get profit margin percentage
     */
    public function getProfitMarginAttribute()
    {
        if ($this->purchase_price > 0) {
            return (($this->sell_price - $this->purchase_price) / $this->purchase_price) * 100;
        }
        return 0;
    }

    /**
     * Get status based on quantities
     */
    public function getStatusAttribute()
    {
        if ($this->return_quantity > 0) {
            return 'Partially Returned';
        }
        if ($this->damage_quantity > 0) {
            return 'Partially Damaged';
        }
        if ($this->lost_quantity > 0) {
            return 'Partially Lost';
        }
        return 'Active';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'Active':
                return 'bg-success';
            case 'Partially Returned':
                return 'bg-warning';
            case 'Partially Damaged':
                return 'bg-danger';
            case 'Partially Lost':
                return 'bg-secondary';
            default:
                return 'bg-info';
        }
    }

    public function getTotalPurchaseAttribute()
    {
        return $this->orderItemStocks->sum(function ($itemStock) {
             $returnTotal = $itemStock->return_quantity;
            return $itemStock->purchase_price * ($itemStock->quantity - $returnTotal);
        });
    }

    public function getTotalSellAttribute()
    {
        return $this->orderItemStocks->sum(function ($itemStock) {
            $returnTotal = $itemStock->return_quantity;
            return $itemStock->sell_price * ($itemStock->quantity - $returnTotal);
        });
    }

    public function getItemTotalProfitAttribute()
    {
        return $this->orderItemStocks->sum(function ($itemStock) {
            $actualProfit = $itemStock->actual_profit - $itemStock->discount_amount;
            $deductibleAmount = ($itemStock->damage_quantity + $itemStock->lost_quantity) * $itemStock->purchase_price;
            $returnTotal = $itemStock->return_quantity;
            $totalQuantity = $itemStock->quantity;
            $perPieceProfit = $actualProfit / $totalQuantity;
            $adjustedProfit = $actualProfit - (($perPieceProfit * $returnTotal) + $deductibleAmount);
            return $adjustedProfit;
        });
    }


}
