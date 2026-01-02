<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Country;
use Illuminate\Support\Str;

class Vendor extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'uuid',
        'email',
        'mobile',
        'shop_name',
        'contact_person',
        'country_id',
        'full_address',
        'lat',
        'long',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'lat' => 'decimal:8',
        'long' => 'decimal:8',
    ];

    /**
     * Boot method to auto-generate UUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vendor) {
            if (empty($vendor->uuid)) {
                $vendor->uuid = Str::uuid();
            }
        });
    }

    /**
     * Scope for active vendors
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
     * Relationship with Country
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Relationship with VendorAccounts
     */
    public function vendorAccounts()
    {
        return $this->hasMany(VendorAccount::class);
    }

    /**
     * Get vendor balance
     */
    public function getBalanceAttribute()
    {
        return VendorAccount::getVendorBalance($this->id);
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('vendor_image')
            ->singleFile()
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
    }

    /**
     * Get vendor image URL
     */
    public function getVendorImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('vendor_image') ?: asset('assets/images/default-vendor.png');
    }

    /**
     * Get vendor image thumb URL
     */
    public function getVendorImageThumbUrlAttribute()
    {
        return $this->getFirstMediaUrl('vendor_image', 'thumb') ?: asset('assets/images/default-vendor.png');
    }

    public function getDueBalanceAttribute()
    {
        $total_credit = $this->vendorAccounts()->where('type',2)->sum('amount');
        $total_debit = $this->vendorAccounts()->where('type',1)->sum('amount');
        return $total_debit - $total_credit;
    }

    public function getOldDueAttribute()
{
    $total_credit = $this->vendorAccounts()
        ->where('type', 2)
        ->whereNull('order_id')
        ->sum('amount');

    $total_debit = $this->vendorAccounts()
        ->where('type', 1)
        ->whereNull('order_id')
        ->sum('amount');

    return $total_credit - $total_debit;
}

}
