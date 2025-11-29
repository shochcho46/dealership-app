<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class BankAccountDetail extends Model
{
    protected $fillable = [
        'bank_id',
        'amount',
        'type',
        'transaction_date',
        'note',
        'created_by'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2'
    ];

    /**
     * Boot method to auto-fill created_by
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });
    }

    /**
     * Get the bank that owns this account detail
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Get the user who created this record
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Admin::class, 'created_by');
    }

    /**
     * Get type text
     */
    public function getTypeTextAttribute()
    {
        return $this->type == 1 ? 'Credit' : 'Debit';
    }

    /**
     * Get type badge class
     */
    public function getTypeBadgeClassAttribute()
    {
        return $this->type == 1 ? 'bg-success' : 'bg-danger';
    }

    /**
     * Scope for credit transactions
     */
    public function scopeCredit($query)
    {
        return $query->where('type', 1);
    }

    /**
     * Scope for debit transactions
     */
    public function scopeDebit($query)
    {
        return $query->where('type', 2);
    }
}
