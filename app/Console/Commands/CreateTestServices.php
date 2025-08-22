<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;

class CreateTestServices extends Command
{
    protected $signature = 'test:create-services {--count=5 : Number of test services to create}';
    protected $description = 'Create test services for calendar testing';

    public function handle()
    {
        $count = $this->option('count');
        
        // Get or create a test client
        $client = User::where('role', 'client')->first();
        if (!$client) {
            $client = User::create([
                'name' => 'Cliente de Prueba',
                'email' => 'cliente.prueba@test.com',
                'password' => bcrypt('password'),
                'role' => 'client',
                'phone' => '+593999123456',
                'city' => 'Quito',
                'status' => 'active'
            ]);
            $this->info("Created test client: {$client->name}");
        }
        
        // Get or create a test technician
        $technician = User::where('role', 'technician')->first();
        if (!$technician) {
            $technician = User::create([
                'name' => 'Técnico de Prueba',
                'email' => 'tecnico.prueba@test.com',
                'password' => bcrypt('password'),
                'role' => 'technician',
                'phone' => '+593999789012',
                'city' => 'Quito',
                'status' => 'active'
            ]);
            $this->info("Created test technician: {$technician->name}");
        }
        
        $this->info("Creating {$count} test services...");
        
        $serviceTypes = ['residential', 'commercial', 'industrial', 'emergency'];
        $statuses = ['scheduled', 'in_progress'];
        
        for ($i = 0; $i < $count; $i++) {
            $date = Carbon::now()->addDays(rand(1, 30));
            $time = sprintf('%02d:%02d', rand(8, 17), rand(0, 3) * 15); // Times like 08:00, 08:15, etc.
            
            $service = Service::create([
                'user_id' => $client->id,
                'technician_id' => rand(0, 1) ? $technician->id : null,
                'type' => $serviceTypes[array_rand($serviceTypes)],
                'description' => "Servicio de prueba " . ($i + 1) . " - Control de plagas",
                'address' => "Dirección de prueba " . ($i + 1) . ", Quito, Ecuador",
                'scheduled_date' => $date->format('Y-m-d'),
                'scheduled_time' => $time,
                'status' => $statuses[array_rand($statuses)],
                'cost' => rand(50, 200),
                'notes' => "Notas del servicio de prueba " . ($i + 1)
            ]);
            
            $this->line("Created service {$service->id}: {$service->scheduled_date} at {$service->scheduled_time}");
        }
        
        // Also create a few services with problematic dates to test the fix
        $this->info("Creating services with potentially problematic dates...");
        
        // Service with invalid time
        try {
            $problemService1 = Service::create([
                'user_id' => $client->id,
                'technician_id' => $technician->id,
                'type' => 'residential',
                'description' => 'Servicio con fecha problemática 1',
                'address' => 'Dirección problemática 1',
                'scheduled_date' => '2025-08-25',
                'scheduled_time' => '25:99', // Invalid time
                'status' => 'scheduled',
                'cost' => 100
            ]);
            $this->line("Created problematic service {$problemService1->id}");
        } catch (\Exception $e) {
            $this->error("Could not create problematic service 1: " . $e->getMessage());
        }
        
        // Service with NULL time
        try {
            $problemService2 = Service::create([
                'user_id' => $client->id,
                'technician_id' => $technician->id,
                'type' => 'commercial',
                'description' => 'Servicio con fecha problemática 2',
                'address' => 'Dirección problemática 2',
                'scheduled_date' => '2025-08-26',
                'scheduled_time' => null, // NULL time
                'status' => 'scheduled',
                'cost' => 150
            ]);
            $this->line("Created problematic service {$problemService2->id}");
        } catch (\Exception $e) {
            $this->error("Could not create problematic service 2: " . $e->getMessage());
        }
        
        $this->info("Test services created successfully!");
        $this->comment("You can now test the calendar with: php artisan calendar:diagnose");
        
        return 0;
    }
}