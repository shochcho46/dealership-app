<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_order_id',
        'product_id',
        'product_name',
        'measurement_unit',
        'package_unit',
        'quantity',
        'damage_quantity',
        'lost_quantity',
        'damage_price',
        'lost_price',
        'price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'damage_quantity' => 'integer',
        'lost_quantity' => 'integer',
        'damage_price' => 'decimal:2',
        'lost_price' => 'decimal:2',
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function companyOrder()
    {
        return $this->belongsTo(CompanyOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
