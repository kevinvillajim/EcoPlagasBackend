<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminSetting;
use Illuminate\Support\Facades\DB;

class FixAdminSettings extends Command
{
    protected $signature = 'admin:fix-settings {--dry-run : Show what would be fixed without making changes}';
    protected $description = 'Fix corrupted admin settings data';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('Diagnosing admin settings...');
        
        // Get all settings directly from database
        $settings = DB::table('admin_settings')->get();
        
        $this->info("Found {$settings->count()} settings in database:");
        
        foreach ($settings as $setting) {
            $this->line("Key: {$setting->key}");
            $this->line("Raw value type: " . gettype($setting->value));
            $this->line("Raw value preview: " . substr($setting->value, 0, 100) . "...");
            
            // Try to decode the JSON
            $decoded = json_decode($setting->value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error("JSON decode error for {$setting->key}: " . json_last_error_msg());
            } else {
                $this->info("JSON is valid for {$setting->key}");
                $this->line("Decoded type: " . gettype($decoded));
                
                if (is_array($decoded)) {
                    $this->line("Array keys: " . implode(', ', array_keys($decoded)));
                }
            }
            
            $this->line("---");
        }
        
        // Test the AdminSetting model methods
        $this->info("\nTesting AdminSetting model:");
        
        $businessHours = AdminSetting::get('business_hours');
        $this->line("AdminSetting::get('business_hours') type: " . gettype($businessHours));
        
        if (is_string($businessHours)) {
            $this->error("business_hours is returning a string instead of array!");
            $this->line("String preview: " . substr($businessHours, 0, 100) . "...");
            
            // Try to fix it
            if (!$isDryRun) {
                $this->info("Attempting to fix business_hours...");
                $decoded = json_decode($businessHours, true);
                if ($decoded !== null) {
                    AdminSetting::set('business_hours', $decoded);
                    $this->info("Fixed business_hours!");
                } else {
                    $this->error("Could not decode business_hours JSON");
                }
            } else {
                $this->comment("DRY RUN: Would attempt to fix business_hours");
            }
        } else {
            $this->info("business_hours is correctly returning an array");
        }
        
        // Test all settings
        $allSettings = AdminSetting::getAllSettings();
        $this->info("\nAdminSetting::getAllSettings() result:");
        foreach ($allSettings as $category => $data) {
            $this->line("{$category}: " . gettype($data));
            if (is_string($data)) {
                $this->error("Category {$category} is a string instead of array!");
                
                if (!$isDryRun) {
                    $this->info("Attempting to fix {$category}...");
                    $decoded = json_decode($data, true);
                    if ($decoded !== null) {
                        $key = match($category) {
                            'businessHours' => 'business_hours',
                            'serviceSettings' => 'service_settings',
                            'pricingSettings' => 'pricing_settings',
                            'notificationSettings' => 'notification_settings',
                            default => $category
                        };
                        AdminSetting::set($key, $decoded);
                        $this->info("Fixed {$category}!");
                    } else {
                        $this->error("Could not decode {$category} JSON");
                    }
                } else {
                    $this->comment("DRY RUN: Would attempt to fix {$category}");
                }
            }
        }
        
        if ($isDryRun) {
            $this->comment("\nThis was a dry run. Use without --dry-run to apply fixes.");
        } else {
            $this->info("\nSettings diagnosis and repair completed!");
        }
        
        return 0;
    }
}