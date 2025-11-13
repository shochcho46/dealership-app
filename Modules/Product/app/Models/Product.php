<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'color_id',
        'company_id',
        'measurement_unit_name',
        'measurement_unit_number',
        'package_unit_name',
        'package_unit_quantity',
        'unit_id',
        'discount_type',
        'discount_amount',
        'description',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Scope for active products
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
     * Relationship with Color
     */
    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    /**
     * Relationship with Unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relationship with Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relationship with Brand (BelongsToMany)
     */
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'brand_product', 'product_id', 'brand_id');
    }

    /**
     * Relationship with Tag (BelongsToMany)
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tag', 'product_id', 'tag_id');
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']);

        $this->addMediaCollection('product_other_image')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']);
    }

    /**
     * Register media conversions
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);

        $this->addMediaConversion('medium')
            ->width(600)
            ->height(600)
            ->sharpen(10);
    }

    /**
     * Get product thumbnail URL
     */
    public function getProductImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('product_image') ?: asset('assets/images/default-product.png');
    }

    /**
     * Get product thumbnail URL
     */
    public function getProductImageThumbUrlAttribute()
    {
        return $this->getFirstMediaUrl('product_image', 'thumb') ?: asset('assets/images/default-product.png');
    }

    /**
     * Get all other product images
     */
    public function getOtherImagesAttribute()
    {
        return $this->getMedia('product_other_image');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
