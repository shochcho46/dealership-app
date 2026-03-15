<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FinancialReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_date',
        'end_date',
        'total_sales',
        'actual_collected_amount',
        'total_purchase',
        'total_expense',
        'discount_amount',
        'total_lost_amount',
        'total_damage_amount',
        'total_profit',
        'profit_for_shareholders',
        'profit_for_sadaqah',
        'profit_to_retain',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_sales' => 'decimal:2',
        'actual_collected_amount' => 'decimal:2',
        'total_purchase' => 'decimal:2',
        'total_expense' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_lost_amount' => 'decimal:2',
        'total_damage_amount' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'profit_for_shareholders' => 'decimal:2',
        'profit_for_sadaqah' => 'decimal:2',
        'profit_to_retain' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (is_null($model->created_by)) {
                $model->created_by = Auth::id();
            }
        });
    }

    /**
     * Get the creator of the report.
     */
    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get the net profit (based on actual collected amount).
     * Formula: actual_collected_amount - (total_purchase + total_expense + discount_amount + total_lost_amount + total_damage_amount)
     */
    public function getNetProfitAttribute()
    {
        return $this->actual_collected_amount - $this->total_purchase - $this->total_expense - $this->discount_amount - $this->total_lost_amount - $this->total_damage_amount;
    }

    /**
     * Get the outstanding amount (not yet collected).
     */
    public function getOutstandingAmountAttribute()
    {
        return $this->total_sales - $this->actual_collected_amount;
    }

    /**
     * Get the actual profit (same as net profit - based on collected amount).
     */
    public function getActualProfitAttribute()
    {
        return $this->actual_collected_amount - $this->total_purchase - $this->total_expense - $this->discount_amount - $this->total_lost_amount - $this->total_damage_amount;
    }

    /**
     * Get the expected profit (based on total sales if all collected).
     */
    public function getExpectedProfitAttribute()
    {
        return $this->total_sales - $this->total_purchase - $this->total_expense - $this->discount_amount - $this->total_lost_amount - $this->total_damage_amount;
    }

    /**
     * Get the collection percentage.
     */
    public function getCollectionPercentageAttribute()
    {
        return $this->total_sales > 0 ? ($this->actual_collected_amount / $this->total_sales) * 100 : 0;
    }

    /**
     * Get total distributions.
     */
    public function getTotalDistributionsAttribute()
    {
        return $this->profit_for_shareholders + $this->profit_for_sadaqah + $this->profit_to_retain;
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            return $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            });
        } elseif ($startDate) {
            return $query->where('end_date', '>=', $startDate);
        } elseif ($endDate) {
            return $query->where('start_date', '<=', $endDate);
        }

        return $query;
    }
}
