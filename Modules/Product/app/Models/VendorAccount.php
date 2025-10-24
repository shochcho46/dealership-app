<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

class VendorAccount extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'vendor_id',
        'order_id',
        'payment_method_id',
        'amount',
        'type',
        'note',
        'collection_date',
        'created_by',
        'deposite_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'type' => 'integer',
        'collection_date' => 'date',
    ];

    /**
     * Boot method to set created_by automatically
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Auth::guard('admin')->check() && !$model->created_by) {
                $model->created_by = Auth::guard('admin')->id();
            }
        });
    }

    /**
     * Relationship with Vendor
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Relationship with Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship with PaymentMethod
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Relationship with Admin (created_by)
     */
    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Relationship with Admin (deposite_by)
     */
    public function depositeBy()
    {
        return $this->belongsTo(Admin::class, 'deposite_by');
    }

    /**
     * Get transaction type text
     */
    public function getTypeTextAttribute()
    {
        switch ($this->type) {
            case 1:
                return 'Debit';
            case 2:
                return 'Credit';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get transaction type badge class
     */
    public function getTypeBadgeClassAttribute()
    {
        switch ($this->type) {
            case 1:
                return 'bg-danger'; // Debit
            case 2:
                return 'bg-success'; // Credit
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Scope for debit transactions
     */
    public function scopeDebit($query)
    {
        return $query->where('type', 1);
    }

    /**
     * Scope for credit transactions
     */
    public function scopeCredit($query)
    {
        return $query->where('type', 2);
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('invoice_receipts')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp', 'application/pdf']);

        $this->addMediaCollection('payment_document')
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
            ->performOnCollections('invoice_receipts');
    }

    /**
     * Get invoice/receipt files
     */
    public function getInvoiceReceiptsAttribute()
    {
        return $this->getMedia('invoice_receipts');
    }

    /**
     * Calculate vendor balance
     */
    public static function getVendorBalance($vendorId)
    {
        $credits = self::where('vendor_id', $vendorId)->where('type', 2)->sum('amount');
        $debits = self::where('vendor_id', $vendorId)->where('type', 1)->sum('amount');
        
        return $credits - $debits;
    }
}