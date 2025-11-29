<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitDistributeDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'profit_distribute_id',
        'amount',
        'type',
        'date',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'type' => 'integer',
        'date' => 'date'
    ];

    /**
     * Relationship with ProfitDistribute
     */
    public function profitDistribute()
    {
        return $this->belongsTo(ProfitDistribute::class);
    }

    /**
     * Get type text
     */
    public function getTypeTextAttribute()
    {
        switch ($this->type) {
            case 1:
                return 'Credit';
            case 2:
                return 'Debit';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get type badge class
     */
    public function getTypeBadgeClassAttribute()
    {
        switch ($this->type) {
            case 1:
                return 'bg-success'; // Credit
            case 2:
                return 'bg-danger'; // Debit
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Scope for credit type
     */
    public function scopeCredit($query)
    {
        return $query->where('type', 1);
    }

    /**
     * Scope for debit type
     */
    public function scopeDebit($query)
    {
        return $query->where('type', 2);
    }
}
