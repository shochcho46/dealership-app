<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'stock_id',
        'product_id',
        'system_qty',
        'physical_qty',
        'damage_qty',
        'lost_qty',
        'damage_amount',
        'lost_amount',
        'avg_purchase_price',
        'remarks'
    ];

    protected $casts = [
        'damage_amount' => 'decimal:2',
        'lost_amount' => 'decimal:2',
        'avg_purchase_price' => 'decimal:2',
    ];

    /**
     * Relationship with Inspection
     */
    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

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
     * Get variance quantity
     */
    public function getVarianceQtyAttribute()
    {
        return $this->physical_qty - $this->system_qty;
    }
}
