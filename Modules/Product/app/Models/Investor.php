<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company',
        'start_date',
        'end_date',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    /**
     * Scope for active investors
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
     * Relationship with InvestmentDetail
     */
    public function investmentDetails()
    {
        return $this->hasMany(InvestmentDetail::class);
    }

    /**
     * Relationship with ProfitDisbursement
     */
    public function profitDisbursements()
    {
        return $this->hasMany(ProfitDisbursement::class);
    }

    /**
     * Get total investment
     */
    public function getTotalInvestmentAttribute()
    {
        return $this->investmentDetails()->sum('amount');
    }

    /**
     * Get total disbursed profit
     */
    public function getTotalDisbursedProfitAttribute()
    {
        return $this->profitDisbursements()->sum('amount');
    }
}
