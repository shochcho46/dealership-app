<?php

namespace Modules\Admin\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Business extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'company_name',
        'mobile_one',
        'mobile_two',
        'email',
        'address',
        'brand_name',
        'delivery_fee_enabled',
        'delivery_fee',
        'inquiry_sms_enabled',
    ];

    protected $casts = [
        'delivery_fee_enabled' => 'boolean',
        'delivery_fee'         => 'decimal:2',
        'inquiry_sms_enabled'  => 'boolean',
    ];

    /**
     * Delivery fee added to every website order (0 when the fee is switched off).
     */
    public function currentDeliveryFee(): float
    {
        return $this->delivery_fee_enabled ? (float) $this->delivery_fee : 0.0;
    }

    /**
     * Short business name for SMS signatures.
     */
    public function shortName(): string
    {
        return trim($this->brand_name ?: $this->company_name ?: config('app.name'));
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp']);
    }
}
