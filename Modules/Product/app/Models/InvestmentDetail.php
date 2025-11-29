<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class InvestmentDetail extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'investor_id',
        'amount',
        'investment_date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'investment_date' => 'date'
    ];

    /**
     * Relationship with Investor
     */
    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('investment_invoice')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp', 'application/pdf'])
            ->singleFile();
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
            ->performOnCollections('investment_invoice');
    }

    /**
     * Get invoice file
     */
    public function getInvoiceFileAttribute()
    {
        return $this->getFirstMedia('investment_invoice');
    }

    /**
     * Get invoice file URL
     */
    public function getInvoiceFileUrlAttribute()
    {
        $media = $this->getFirstMedia('investment_invoice');
        return $media ? $media->getUrl() : null;
    }
}
