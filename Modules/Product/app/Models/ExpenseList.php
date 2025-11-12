<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseList extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_head_id',
        'title',
        'description',
        'amount',
        'expense_date',
        'reference_no',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'amount' => 'decimal:2',
        'expense_date' => 'date'
    ];

    /**
     * Scope for active expenses
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
     * Relationship with ExpenseHead
     */
    public function expenseHead()
    {
        return $this->belongsTo(ExpenseHead::class);
    }
}
