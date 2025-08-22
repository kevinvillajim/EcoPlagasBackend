<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ServiceSetting;
use App\Http\Controllers\API\Client\ServiceController;

class TestServiceTypes extends Command
{
    protected $signature = 'test:service-types';
    protected $description = 'Test service types availability';

    public function handle()
    {
        $this->info('Testing service types...');
        
        // Test direct model query
        $this->info('1. Testing ServiceSetting model directly:');
        
        $allSettings = ServiceSetting::all();
        $this->line("Total service_settings records: " . $allSettings->count());
        
        foreach ($allSettings as $setting) {
            $status = $setting->is_active ? '✅ ACTIVE' : '❌ INACTIVE';
            $this->line("  {$setting->service_type}: {$status} (Duration: {$setting->duration_minutes}min, Price: \${$setting->min_price}-\${$setting->max_price})");
        }
        
        // Test getActive() method
        $this->info("\n2. Testing ServiceSetting::getActive():");
        $activeSettings = ServiceSetting::getActive();
        $this->line("Active service_settings: " . $activeSettings->count());
        
        foreach ($activeSettings as $setting) {
            $this->line("  {$setting->service_type}: Duration {$setting->duration_minutes}min, Price \${$setting->min_price}-\${$setting->max_price}");
        }
        
        // Test the controller method
        $this->info("\n3. Testing ServiceController::getServiceTypes():");
        
        try {
            $controller = new ServiceController();
            $response = $controller->getServiceTypes();
            $data = $response->getData(true);
            
            if ($data['success']) {
                $serviceTypes = $data['service_types'];
                $this->info("Controller returned " . count($serviceTypes) . " service types:");
                
                foreach ($serviceTypes as $type) {
                    $this->line("  {$type['id']}: {$type['name']} ({$type['description']})");
                    $this->line("    Price: {$type['price_range']}, Duration: {$type['duration_hours']}h");
                }
            } else {
                $this->error("Controller failed: " . ($data['message'] ?? 'Unknown error'));
            }
            
        } catch (\Exception $e) {
            $this->error("Exception in controller: " . $e->getMessage());
        }
        
        // Test expected service types
        $this->info("\n4. Expected service types from frontend:");
        $expectedTypes = ['residential', 'commercial', 'industrial', 'emergency'];
        $availableTypes = $activeSettings->pluck('service_type')->toArray();
        
        foreach ($expectedTypes as $type) {
            if (in_array($type, $availableTypes)) {
                $this->info("  ✅ {$type} - Available");
            } else {
                $this->error("  ❌ {$type} - Missing");
            }
        }
        
        return 0;
    }
}