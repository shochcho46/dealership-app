<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitDisbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'investor_id',
        'amount',
        'disbursement_date',
        'note'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'disbursement_date' => 'date'
    ];

    /**
     * Relationship with Investor
     */
    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }
}
