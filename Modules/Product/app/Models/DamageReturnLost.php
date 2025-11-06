<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DamageReturnLost extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'damage_return_losts';
    protected $fillable = [
        'product_id',
        'stock_id',
        'order_id',
        'order_item_id',
        'quantity',
        'status',
        'purchase_price',
        'total_price',
        'order_item_stock_id',
        'reason'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'status' => 'integer',
        'purchase_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'order_item_stock_id' => 'integer',
    ];

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('evidence_pic')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg'])
            ->singleFile(false);
    }

    /**
     * Register media conversions
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('evidence_pic');

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600)
            ->performOnCollections('evidence_pic');
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // When a record is created, updated, or deleted,
        // trigger updates to related tables
        static::created(function ($model) {
            $model->updateRelatedTables('created');
        });

        static::updated(function ($model) {
            $model->updateRelatedTables('updated');
        });

        static::deleted(function ($model) {
            $model->updateRelatedTables('deleted');
        });
    }

    /**
     * Update related tables when DamageReturnLost record changes
     */
    public function updateRelatedTables($action)
    {
        try {
            // This method can be used for additional business logic
            // The main updates are handled in the controller for better control
            Log::info("DamageReturnLost record {$action}: ID {$this->id}");
        } catch (\Exception $e) {
            Log::error("Error updating related tables for DamageReturnLost ID {$this->id}: " . $e->getMessage());
        }
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
     * Relationship with Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship with OrderItem
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Relationship with OrderItemStock
     */
    public function orderItemStock()
    {
        return $this->belongsTo(OrderItemStock::class);
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case 1:
                return 'damage';
            case 2:
                return 'return';
            case 3:
                return 'lost';
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
                return 'bg-danger'; // Return
            case 3:
                return 'bg-secondary'; // Lost
            default:
                return 'bg-dark'; // Unknown
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
