<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_number',
        'inspection_date',
        'notes',
        'total_damage_amount',
        'total_lost_amount',
        'total_damage_qty',
        'total_lost_qty',
        'inspected_by'
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'total_damage_amount' => 'decimal:2',
        'total_lost_amount' => 'decimal:2',
    ];

    /**
     * Boot method to auto-generate inspection number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($inspection) {
            if (empty($inspection->inspection_number)) {
                $inspection->inspection_number = $inspection->generateInspectionNumber();
            }
        });
    }

    /**
     * Generate unique inspection number
     */
    public function generateInspectionNumber()
    {
        $date = now()->format('Ymd');
        $lastInspection = static::whereDate('created_at', now())->max('id') ?? 0;
        $nextId = $lastInspection + 1;

        return "INS-{$date}-" . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationship with Admin (inspected_by)
     */
    public function inspectedBy()
    {
        return $this->belongsTo(Admin::class, 'inspected_by');
    }

    /**
     * Relationship with InspectionItems
     */
    public function items()
    {
        return $this->hasMany(InspectionItem::class);
    }
}
