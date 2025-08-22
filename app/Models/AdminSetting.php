<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminSetting extends Model
{
    protected $fillable = [
        'key',
        'value'
    ];

    protected $casts = [
        'value' => 'array'
    ];

    /**
     * Get setting by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        // Check for corrupted data before returning
        if (self::isDataCorrupted($setting->value)) {
            \Log::warning("AdminSetting: Detected corrupted data for key '{$key}', attempting to fix...");
            
            // Try to fix the corruption
            $fixed = self::fixCorruptedData($setting->value);
            if ($fixed !== null) {
                // Update the database with fixed data
                $setting->value = $fixed;
                $setting->save();
                \Log::info("AdminSetting: Successfully fixed corrupted data for key '{$key}'");
                return $fixed;
            } else {
                \Log::error("AdminSetting: Could not fix corrupted data for key '{$key}', returning default");
                return $default;
            }
        }
        
        return $setting->value;
    }

    /**
     * Check if data appears to be corrupted
     */
    private static function isDataCorrupted($value): bool
    {
        return is_array($value) && 
               isset($value[0]) && 
               is_string($value[0]) && 
               count($value) > 50 && 
               strlen($value[0]) === 1;
    }

    /**
     * Attempt to fix corrupted data
     */
    private static function fixCorruptedData($corruptedValue)
    {
        if (!self::isDataCorrupted($corruptedValue)) {
            return null;
        }

        try {
            $reconstructed = implode('', $corruptedValue);
            $decoded = json_decode($reconstructed, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        } catch (\Exception $e) {
            \Log::error("AdminSetting: Exception while fixing corrupted data: " . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Set setting by key
     */
    public static function set(string $key, $value): void
    {
        // Validate that the value is properly formatted
        if (is_string($value)) {
            // If it's a string, try to decode it as JSON
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error("AdminSetting: Attempted to save invalid JSON string for key '{$key}': " . substr($value, 0, 100));
                throw new \InvalidArgumentException("Invalid JSON data provided for setting '{$key}'");
            }
            $value = $decoded;
        }
        
        // Ensure we're not saving corrupted array data
        if (is_array($value) && isset($value[0]) && is_string($value[0]) && count($value) > 50) {
            // This looks like a corrupted JSON string saved as character array
            \Log::error("AdminSetting: Detected potential corrupted array data for key '{$key}'. Attempting to reconstruct.");
            $reconstructed = implode('', $value);
            $decoded = json_decode($reconstructed, true);
            if ($decoded !== null) {
                \Log::info("AdminSetting: Successfully reconstructed corrupted data for key '{$key}'");
                $value = $decoded;
            } else {
                \Log::error("AdminSetting: Failed to reconstruct corrupted data for key '{$key}'");
                throw new \InvalidArgumentException("Corrupted array data detected for setting '{$key}'");
            }
        }

        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all settings grouped by category
     */
    public static function getAllSettings(): array
    {
        $settings = self::all()->pluck('value', 'key')->toArray();
        
        return [
            'businessHours' => $settings['business_hours'] ?? [],
            'serviceSettings' => $settings['service_settings'] ?? [],
            'pricingSettings' => $settings['pricing_settings'] ?? [],
            'notificationSettings' => $settings['notification_settings'] ?? []
        ];
    }

    /**
     * Update multiple settings at once
     */
    public static function updateSettings(array $settings): void
    {
        foreach ($settings as $category => $values) {
            $key = match($category) {
                'businessHours' => 'business_hours',
                'serviceSettings' => 'service_settings', 
                'pricingSettings' => 'pricing_settings',
                'notificationSettings' => 'notification_settings',
                default => $category
            };
            
            self::set($key, $values);
        }
    }
}
