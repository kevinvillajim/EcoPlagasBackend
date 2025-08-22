<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSetting extends Model
{
    protected $fillable = [
        'service_type',
        'duration_minutes',
        'min_price',
        'max_price',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2'
    ];

    // Service type constants
    const TYPE_RESIDENTIAL = 'residential';
    const TYPE_COMMERCIAL = 'commercial';
    const TYPE_INDUSTRIAL = 'industrial';
    const TYPE_EMERGENCY = 'emergency';

    /**
     * Get available service types
     */
    public static function getServiceTypes(): array
    {
        return [
            self::TYPE_RESIDENTIAL,
            self::TYPE_COMMERCIAL,
            self::TYPE_INDUSTRIAL,
            self::TYPE_EMERGENCY
        ];
    }

    /**
     * Get duration in hours for specific service type
     */
    public static function getDurationForType(string $type): float
    {
        $setting = self::where('service_type', $type)->first();
        return $setting ? ($setting->duration_minutes / 60) : 2; // Default 2 hours
    }

    /**
     * Get duration in minutes for specific service type
     */
    public static function getDurationInMinutesForType(string $type): int
    {
        $setting = self::where('service_type', $type)->first();
        return $setting ? $setting->duration_minutes : 120; // Default 120 minutes
    }

    /**
     * Get all active service settings
     */
    public static function getActive()
    {
        return self::where('is_active', true)->get();
    }
}
