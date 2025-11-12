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
     * Get total expenses for this head
     */
    public function getTotalExpensesAttribute()
    {
        return $this->expenseLists()->sum('amount');
    }

    /**
     * Get remaining amount
     */
    public function getRemainingAmountAttribute()
    {
        return $this->max_amount - $this->total_expenses;
    }
}
