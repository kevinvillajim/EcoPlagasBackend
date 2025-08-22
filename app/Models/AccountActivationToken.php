<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AccountActivationToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new activation token
     */
    public static function createToken(User $user)
    {
        // Invalidate any existing tokens for this user
        static::where('user_id', $user->id)->where('used', false)->delete();
        
        return static::create([
            'user_id' => $user->id,
            'token' => Str::random(64),
            'expires_at' => now()->addHours(24), // Token válido por 24 horas
            'used' => false,
        ]);
    }

    /**
     * Check if token is valid
     */
    public function isValid()
    {
        return !$this->used && $this->expires_at > now();
    }

    /**
     * Mark token as used
     */
    public function markAsUsed()
    {
        $this->update(['used' => true]);
    }

    /**
     * Find valid token
     */
    public static function findValidToken($token)
    {
        return static::where('token', $token)
                    ->where('used', false)
                    ->where('expires_at', '>', now())
                    ->first();
    }
}