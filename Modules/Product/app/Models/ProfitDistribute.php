<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitDistribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'month',
        'year',
        'total_amount',
        'status'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'status' => 'boolean',
        'month' => 'integer',
        'year' => 'integer'
    ];

    /**
     * Scope for active profit distributions
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
     * Relationship with ProfitDistributeDetail
     */
    public function profitDistributeDetails()
    {
        return $this->hasMany(ProfitDistributeDetail::class);
    }

    /**
     * Get month name
     */
    public function getMonthNameAttribute()
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        return $months[$this->month] ?? 'Unknown';
    }

    /**
     * Get period text
     */
    public function getPeriodTextAttribute()
    {
        return $this->month_name . ' ' . $this->year;
    }

    /**
     * Get total credit
     */
    public function getTotalCreditAttribute()
    {
        return $this->profitDistributeDetails()->where('type', 1)->sum('amount');
    }

    /**
     * Get total debit
     */
    public function getTotalDebitAttribute()
    {
        return $this->profitDistributeDetails()->where('type', 2)->sum('amount');
    }

    /**
     * Get balance
     */
    public function getBalanceAttribute()
    {
        return $this->total_credit - $this->total_debit;
    }
}
