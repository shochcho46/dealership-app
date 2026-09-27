<?php

namespace Modules\Product\Models;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RoutePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    protected $casts = [
        'created_by' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($routePlan) {
            if (Auth::guard('admin')->check() && !$routePlan->created_by) {
                $routePlan->created_by = Auth::guard('admin')->id();
            }
        });
    }

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
