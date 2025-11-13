<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseHead extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'max_amount'
    ];

    protected $casts = [
        'status' => 'boolean',
        'max_amount' => 'decimal:2'
    ];

    /**
     * Scope for active expense heads
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
     * Relationship with ExpenseList
     */
    public function expenseLists()
    {
        return $this->hasMany(ExpenseList::class);
    }

    /**
     * Get total expenses for this head (all time)
     */
    public function getTotalExpensesAttribute()
    {
        return $this->expenseLists()->sum('amount');
    }

    /**
     * Get remaining amount (all time)
     */
    public function getRemainingAmountAttribute()
    {
        return $this->max_amount - $this->total_expenses;
    }

    /**
     * Get total expenses for current month
     */
    public function getTotalExpensesCurrentMonthAttribute()
    {
        return $this->expenseLists()
            ->whereYear('expense_date', now()->year)
            ->whereMonth('expense_date', now()->month)
            ->sum('amount');
    }

    /**
     * Get remaining amount for current month
     */
    public function getRemainingAmountCurrentMonthAttribute()
    {
        return $this->max_amount - $this->total_expenses_current_month;
    }

    /**
     * Get total expenses for current month using query
     */
    public function getTotalExpensesForCurrentMonth()
    {
        return $this->expenseLists()
            ->whereYear('expense_date', now()->year)
            ->whereMonth('expense_date', now()->month)
            ->sum('amount');
    }

    /**
     * Get remaining amount for current month using query
     */
    public function getRemainingAmountForCurrentMonth()
    {
        return $this->max_amount - $this->getTotalExpensesForCurrentMonth();
    }
}
