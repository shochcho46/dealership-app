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
     * Get the net profit (total profit minus distributions).
     */
    public function getNetProfitAttribute()
    {
        return $this->total_sales - $this->total_purchase - $this->total_expense - $this->discount_amount - $this->total_lost_amount - $this->total_damage_amount;
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
