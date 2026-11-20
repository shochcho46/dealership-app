<?php

namespace Modules\Product\Models;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class WebsiteInquiry extends Model
{
    public const STATUS_NEW = 0;
    public const STATUS_CONTACTED = 1;
    public const STATUS_CLOSED = 2;

    public const STATUSES = [
        self::STATUS_NEW       => 'New',
        self::STATUS_CONTACTED => 'Contacted',
        self::STATUS_CLOSED    => 'Closed',
    ];

    protected $fillable = [
        'name',
        'shop_name',
        'mobile',
        'area',
        'delivery_type',
        'message',
        'product_id',
        'items',
        'status',
        'handled_by',
        'ip',
    ];

    public const DELIVERY_TYPES = [
        'shop' => 'Shop',
        'home' => 'Home',
    ];

    protected $casts = [
        'items'  => 'array',
        'status' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // Record which admin changed the status.
        static::updating(function (WebsiteInquiry $inquiry) {
            if ($inquiry->isDirty('status') && Auth::guard('admin')->check()) {
                $inquiry->handled_by = Auth::guard('admin')->id();
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function handledBy()
    {
        return $this->belongsTo(Admin::class, 'handled_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'New';
    }

    public function getWhatsappNumberAttribute(): string
    {
        $digits = preg_replace('/\D/', '', $this->mobile);
        if (str_starts_with($digits, '01')) {
            $digits = '88' . $digits;
        }
        return $digits;
    }
}
