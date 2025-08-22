<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'role',
        'is_active',
        'avatar',
        'email_verified_at',
        'document_type',
        'document_number',
        'birth_date',
        'preferences',
        'security_settings',
        'ruc',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'birth_date' => 'date',
        ];
    }

    // User roles constants
    const ROLE_CLIENT = 'client';
    const ROLE_TECHNICIAN = 'technician';
    const ROLE_ADMIN = 'admin';

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is client
     */
    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    /**
     * Check if user is technician
     */
    public function isTechnician(): bool
    {
        return $this->role === self::ROLE_TECHNICIAN;
    }

    /**
     * Services as client
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Services as technician
     */
    public function technicianServices()
    {
        return $this->hasMany(Service::class, 'technician_id');
    }

    /**
     * User certificates
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * User notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * User reviews
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCountAttribute()
    {
        return $this->notifications()->unread()->count();
    }

    /**
     * Get services count for clients
     */
    public function getServicesCountAttribute()
    {
        return $this->services()->count();
    }

    /**
     * Get completed services count for clients
     */
    public function getCompletedServicesCountAttribute()
    {
        return $this->services()->completed()->count();
    }
}
