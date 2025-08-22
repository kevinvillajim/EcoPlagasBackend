<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use App\Models\AdminSetting;
use Carbon\Carbon;

class DiagnoseCalendar extends Command
{
    protected $signature = 'calendar:diagnose {--fix : Fix problematic dates}';
    protected $description = 'Diagnose calendar date issues';

    public function handle()
    {
        $shouldFix = $this->option('fix');
        
        $this->info('Diagnosing calendar dates...');
        
        // Get service settings
        $serviceSettings = AdminSetting::get('service_settings', []);
        $defaultDurationMinutes = $serviceSettings['defaultServiceDuration'] ?? 120;
        $this->line("Default service duration: {$defaultDurationMinutes} minutes");
        
        // Get services with potential date issues
        $services = Service::select([
            'id', 'user_id', 'technician_id', 'type', 'address', 
            'scheduled_date', 'scheduled_time', 'status', 'cost', 'description'
        ])
        ->with(['user:id,name,email', 'technician:id,name,email'])
        ->whereIn('status', ['scheduled', 'in_progress'])
        ->orderBy('scheduled_date')
        ->take(10)
        ->get();
        
        $this->info("Found {$services->count()} services to analyze:");
        
        foreach ($services as $service) {
            $this->line("---");
            $this->line("Service ID: {$service->id}");
            $this->line("Scheduled Date: " . ($service->scheduled_date ?? 'NULL'));
            $this->line("Scheduled Time: " . ($service->scheduled_time ?? 'NULL'));
            $this->line("Status: {$service->status}");
            $this->line("Client: " . ($service->user->name ?? 'N/A'));
            
            // Test the date processing logic from ServiceManagementController
            if ($service->scheduled_date && $service->scheduled_time) {
                try {
                    // Extract date part
                    $dateOnly = substr($service->scheduled_date, 0, 10);
                    $this->line("Date only: {$dateOnly}");
                    
                    // Build start datetime string
                    $startStr = $dateOnly . ' ' . $service->scheduled_time;
                    $this->line("Start string: {$startStr}");
                    
                    // Calculate end time
                    $startTime = new \DateTime($startStr);
                    $endTime = clone $startTime;
                    $endTime->add(new \DateInterval('PT' . $defaultDurationMinutes . 'M'));
                    
                    // Format for calendar
                    $startFormatted = $dateOnly . 'T' . $service->scheduled_time . ':00';
                    $endFormatted = $endTime->format('Y-m-d\TH:i:s');
                    
                    $this->line("Start formatted: {$startFormatted}");
                    $this->line("End formatted: {$endFormatted}");
                    
                    // Check for 1969/1970 dates
                    if (strpos($endFormatted, '1969') !== false || strpos($endFormatted, '1970') !== false) {
                        $this->error("⚠️  PROBLEM: End date shows {$endFormatted}");
                        
                        if ($shouldFix) {
                            $this->info("Attempting to fix...");
                            // Try to fix by ensuring proper datetime format
                            $fixedDate = Carbon::parse($service->scheduled_date)->format('Y-m-d');
                            $fixedTime = $service->scheduled_time;
                            
                            // Update the service
                            $service->scheduled_date = $fixedDate;
                            $service->save();
                            
                            $this->info("Fixed service {$service->id}");
                        }
                    } else {
                        $this->info("✅ Date processing looks good");
                    }
                    
                } catch (\Exception $e) {
                    $this->error("❌ Error processing dates: " . $e->getMessage());
                    
                    if ($shouldFix) {
                        $this->info("Attempting to fix datetime format...");
                        try {
                            // Try to parse and reformat the date
                            if ($service->scheduled_date) {
                                $carbonDate = Carbon::parse($service->scheduled_date);
                                $service->scheduled_date = $carbonDate->format('Y-m-d');
                                $service->save();
                                $this->info("Fixed datetime format for service {$service->id}");
                            }
                        } catch (\Exception $fixError) {
                            $this->error("Could not fix service {$service->id}: " . $fixError->getMessage());
                        }
                    }
                }
            } else {
                $this->error("❌ Missing scheduled_date or scheduled_time");
            }
        }
        
        // Test the API endpoint response format
        $this->line("\n" . str_repeat('=', 50));
        $this->info('Testing API response format...');
        
        try {
            // Simulate the calendar API call
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
            
            $this->line("Date range: {$startDate} to {$endDate}");
            
            $testServices = Service::select([
                'id', 'user_id', 'technician_id', 'type', 'address',
                'scheduled_date', 'scheduled_time', 'status', 'cost', 'description'
            ])
            ->with(['user:id,name,email,phone', 'technician:id,name,email'])
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->take(3)
            ->get();
            
            $this->info("Found {$testServices->count()} services in current month");
            
            foreach ($testServices as $service) {
                $this->line("Processing service {$service->id}...");
                
                // Replicate exact logic from ServiceManagementController
                $dateOnly = substr($service->scheduled_date, 0, 10);
                $startStr = $dateOnly . ' ' . $service->scheduled_time;
                
                try {
                    $startTime = new \DateTime($startStr);
                    $endTime = clone $startTime;
                    $endTime->add(new \DateInterval('PT' . $defaultDurationMinutes . 'M'));
                    
                    $startFormatted = $dateOnly . 'T' . $service->scheduled_time . ':00';
                    $endFormatted = $endTime->format('Y-m-d\TH:i:s');
                    
                    $this->line("  Start: {$startFormatted}");
                    $this->line("  End: {$endFormatted}");
                    
                    if (strpos($endFormatted, '1969') !== false || strpos($endFormatted, '1970') !== false) {
                        $this->error("  ⚠️  ISSUE FOUND!");
                    } else {
                        $this->info("  ✅ OK");
                    }
                } catch (\Exception $e) {
                    $this->error("  ❌ Error: " . $e->getMessage());
                }
            }
            
        } catch (\Exception $e) {
            $this->error('Error testing API: ' . $e->getMessage());
        }
        
        if (!$shouldFix) {
            $this->comment("\nTo fix issues found, run: php artisan calendar:diagnose --fix");
        }
        
        return 0;
    }
}