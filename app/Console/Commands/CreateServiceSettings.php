<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ServiceSetting;

class CreateServiceSettings extends Command
{
    protected $signature = 'create:service-settings {--force : Force recreate existing settings}';
    protected $description = 'Create the 4 required service settings';

    public function handle()
    {
        $force = $this->option('force');
        
        if ($force) {
            $this->info('Force mode: Deleting existing service settings...');
            ServiceSetting::truncate();
        }
        
        $this->info('Creating service settings...');
        
        // Based on the data from dump-ecoplagasbackend-202508221159.sql
        $services = [
            [
                'service_type' => 'residential',
                'duration_minutes' => 180,
                'min_price' => 60.00,
                'max_price' => 150.00,
                'description' => 'Control de Plagas Residencial',
                'is_active' => true
            ],
            [
                'service_type' => 'commercial',
                'duration_minutes' => 180,
                'min_price' => 100.00,
                'max_price' => 500.00,
                'description' => 'Control de Plagas Comercial',
                'is_active' => true
            ],
            [
                'service_type' => 'industrial',
                'duration_minutes' => 240,
                'min_price' => 200.00,
                'max_price' => 1000.00,
                'description' => 'Control de Plagas Industrial',
                'is_active' => true
            ],
            [
                'service_type' => 'emergency',
                'duration_minutes' => 60,
                'min_price' => 80.00,
                'max_price' => 300.00,
                'description' => 'Servicio de Emergencia',
                'is_active' => true
            ]
        ];
        
        foreach ($services as $serviceData) {
            $existing = ServiceSetting::where('service_type', $serviceData['service_type'])->first();
            
            if ($existing && !$force) {
                $this->line("Service {$serviceData['service_type']} already exists, skipping...");
                continue;
            }
            
            if ($existing && $force) {
                $existing->delete();
            }
            
            $service = ServiceSetting::create($serviceData);
            $this->info("✅ Created service: {$service->service_type} (Duration: {$service->duration_minutes}min, Price: \${$service->min_price}-\${$service->max_price})");
        }
        
        // Verify creation
        $this->info("\nVerifying created services:");
        $activeServices = ServiceSetting::getActive();
        $this->line("Total active services: " . $activeServices->count());
        
        foreach ($activeServices as $service) {
            $this->line("  ✅ {$service->service_type}: {$service->description}");
        }
        
        $this->info("\nService settings created successfully!");
        $this->comment("You can now test with: php artisan test:service-types");
        
        return 0;
    }
}