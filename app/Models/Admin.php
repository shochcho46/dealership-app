<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Admin extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, HasRoles, InteractsWithMedia;

    // protected $guard_name = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'otp',
        'status'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the admin's full display name
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ' (' . $this->email . ')';
    }

    /**
     * Check if admin is active
     */
    public function isActive()
    {
        return $this->status === 1;
    }

    /**
     * Get admin roles as comma-separated string
     */
    public function getRolesStringAttribute()
    {
        return $this->roles->pluck('name')->implode(', ');
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_picture')
            ->singleFile()
            ->acceptsMimeTypes([
            'image/jpeg',
            'image/png',
            'image/jpg',
            'image/webp'
        ]);
    }
}
