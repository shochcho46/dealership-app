<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

class DsrCollection extends Model
{
    use HasFactory;

    protected $table = 'dsr_collections';

    protected $fillable = [
        'vendor_id',
        'payment_method_id',
        'amount',
        'collection_date',
        'note',
        'deposite_by',
        'created_by',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'collection_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Auth::guard('admin')->check() && !$model->created_by) {
                $model->created_by = Auth::guard('admin')->id();
            }
        });
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function depositeBy()
    {
        return $this->belongsTo(Admin::class, 'deposite_by');
    }
}
