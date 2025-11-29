<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    protected $fillable = [
        'bank_name',
        'account_name',
        'account_number',
        'branch_name',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    /**
     * Get all account details for this bank
     */
    public function bankAccountDetails(): HasMany
    {
        return $this->hasMany(BankAccountDetail::class);
    }

    /**
     * Get total credit amount
     */
    public function getTotalCreditAttribute()
    {
        return $this->bankAccountDetails()->where('type', 1)->sum('amount');
    }

    /**
     * Get total debit amount
     */
    public function getTotalDebitAttribute()
    {
        return $this->bankAccountDetails()->where('type', 2)->sum('amount');
    }

    /**
     * Get current balance (credit - debit)
     */
    public function getBalanceAttribute()
    {
        return $this->total_credit - $this->total_debit;
    }

    /**
     * Scope active banks
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
