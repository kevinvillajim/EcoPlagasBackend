<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminSetting;

class ValidateSettingsIntegrity extends Command
{
    protected $signature = 'settings:validate-integrity';
    protected $description = 'Validate the integrity of admin settings and detect any corruption';

    public function handle()
    {
        $this->info('Starting settings integrity validation...');

        $corruptedCount = 0;
        $validCount = 0;
        $fixedCount = 0;

        // Get all admin settings
        $settings = AdminSetting::all();

        foreach ($settings as $setting) {
            $this->line("Validating setting: {$setting->key}");
            
            // Check if value is corrupted
            if ($this->isDataCorrupted($setting->value)) {
                $corruptedCount++;
                $this->error("  ❌ CORRUPTED: {$setting->key}");
                
                // Try to fix it
                $fixed = $this->fixCorruptedData($setting->value);
                if ($fixed !== null) {
                    $setting->value = $fixed;
                    $setting->save();
                    $fixedCount++;
                    $this->info("  ✅ FIXED: {$setting->key}");
                } else {
                    $this->warn("  ⚠️  COULD NOT FIX: {$setting->key}");
                }
            } else {
                $validCount++;
                $this->info("  ✅ Valid: {$setting->key}");
            }
        }

        // Summary
        $this->newLine();
        $this->info('=== INTEGRITY VALIDATION SUMMARY ===');
        $this->line("Valid settings: {$validCount}");
        $this->line("Corrupted settings found: {$corruptedCount}");
        $this->line("Settings fixed: {$fixedCount}");
        
        if ($corruptedCount === 0) {
            $this->info('🎉 All settings are valid and uncorrupted!');
            return 0;
        } else if ($fixedCount === $corruptedCount) {
            $this->info('🔧 All corrupted settings have been fixed!');
            return 0;
        } else {
            $this->error('⚠️  Some settings could not be fixed. Manual intervention required.');
            return 1;
        }
    }

    private function isDataCorrupted($value): bool
    {
        return is_array($value) && 
               isset($value[0]) && 
               is_string($value[0]) && 
               count($value) > 50 && 
               strlen($value[0]) === 1;
    }

    private function fixCorruptedData($corruptedValue)
    {
        if (!$this->isDataCorrupted($corruptedValue)) {
            return null;
        }

        try {
            $reconstructed = implode('', $corruptedValue);
            $decoded = json_decode($reconstructed, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        } catch (\Exception $e) {
            $this->error("Exception while fixing corrupted data: " . $e->getMessage());
        }
        
        return null;
    }
}