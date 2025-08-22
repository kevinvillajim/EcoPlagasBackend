<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminSetting;

class FixCorruptedSettings extends Command
{
    protected $signature = 'settings:fix-corrupted';
    protected $description = 'Fix corrupted JSON settings in database';

    public function handle()
    {
        $this->info('Starting to fix corrupted settings...');

        // Get all admin settings
        $settings = AdminSetting::all();

        foreach ($settings as $setting) {
            $this->info("Checking setting: {$setting->key}");
            
            // Check if value is an array of characters (corrupted JSON)
            if (is_array($setting->value) && isset($setting->value[0]) && is_string($setting->value[0])) {
                // Try to reconstruct the JSON string
                $jsonString = implode('', $setting->value);
                $this->warn("Found corrupted setting: {$setting->key}");
                $this->warn("Corrupted value: " . substr(json_encode($setting->value), 0, 100) . "...");
                
                try {
                    // Parse the reconstructed JSON
                    $parsedValue = json_decode($jsonString, true);
                    
                    if ($parsedValue !== null) {
                        $this->info("Successfully parsed JSON for: {$setting->key}");
                        
                        // Update the setting with the correct value
                        $setting->value = $parsedValue;
                        $setting->save();
                        
                        $this->info("Fixed setting: {$setting->key}");
                    } else {
                        $this->error("Failed to parse JSON for: {$setting->key}");
                        
                        // Set default values based on key
                        $defaultValue = $this->getDefaultValue($setting->key);
                        if ($defaultValue !== null) {
                            $setting->value = $defaultValue;
                            $setting->save();
                            $this->info("Set default value for: {$setting->key}");
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("Exception while fixing {$setting->key}: " . $e->getMessage());
                }
            } else {
                $this->info("Setting {$setting->key} appears to be valid");
            }
        }

        $this->info('Finished fixing corrupted settings!');
    }

    private function getDefaultValue($key)
    {
        $defaults = [
            'service_settings' => [
                'defaultServiceDuration' => 120,
                'bufferTimeBetweenServices' => 30,
                'minimumAdvanceDays' => 1,
                'enableAdvanceBooking' => false,
                'allowWeekendBooking' => true
            ],
            'business_hours' => [
                'monday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                'tuesday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                'wednesday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                'thursday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                'friday' => ['open' => '08:00', 'close' => '18:00', 'isOpen' => true],
                'saturday' => ['open' => '08:00', 'close' => '14:00', 'isOpen' => true],
                'sunday' => ['open' => '08:00', 'close' => '12:00', 'isOpen' => false]
            ],
            'pricing_settings' => [
                'currency' => 'USD',
                'baseServicePrice' => 75.00,
                'includeTax' => true,
                'taxRate' => 12,
                'showPrices' => true,
                'emergencyServiceSurcharge' => 50,
                'weekendSurcharge' => 25,
                'servicePrices' => [
                    'residential' => ['min' => 60, 'max' => 150, 'enabled' => true],
                    'commercial' => ['min' => 100, 'max' => 500, 'enabled' => true],
                    'industrial' => ['min' => 200, 'max' => 1000, 'enabled' => true],
                    'emergency' => ['min' => 80, 'max' => 300, 'enabled' => true]
                ]
            ],
            'notification_settings' => [
                'emailNotifications' => true,
                'clientReminders' => true,
                'adminAlerts' => true,
                'reminderHours' => 24,
                'followUpDays' => 7
            ]
        ];

        return $defaults[$key] ?? null;
    }
}