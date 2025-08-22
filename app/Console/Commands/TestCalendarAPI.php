<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\API\Admin\ServiceManagementController;
use Illuminate\Http\Request;

class TestCalendarAPI extends Command
{
    protected $signature = 'test:calendar-api {--start_date=} {--end_date=}';
    protected $description = 'Test calendar API response directly';

    public function handle()
    {
        $startDate = $this->option('start_date') ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $this->option('end_date') ?? now()->endOfMonth()->format('Y-m-d');
        
        $this->info("Testing calendar API for date range: {$startDate} to {$endDate}");
        
        // Create a mock request
        $request = new Request([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        // Create controller instance
        $controller = new ServiceManagementController();
        
        try {
            // Call the actual controller method
            $response = $controller->getCalendarEvents($request);
            $data = $response->getData(true);
            
            if ($data['success']) {
                $events = $data['events'];
                $this->info("API returned " . count($events) . " events:");
                
                foreach ($events as $event) {
                    $this->line("---");
                    $this->line("Event ID: " . $event['id']);
                    $this->line("Title: " . $event['title']);
                    $this->line("Start: " . $event['start']);
                    $this->line("End: " . $event['end']);
                    $this->line("Client: " . ($event['extendedProps']['client_name'] ?? 'N/A'));
                    
                    // Check for 1969/1970 dates
                    if (strpos($event['start'], '1969') !== false || strpos($event['start'], '1970') !== false ||
                        strpos($event['end'], '1969') !== false || strpos($event['end'], '1970') !== false) {
                        $this->error("⚠️  PROBLEM: Found 1969/1970 date in event {$event['id']}");
                    } else {
                        $this->info("✅ Event dates look good");
                    }
                }
                
                if (isset($data['summary'])) {
                    $this->line("\nSummary:");
                    $this->line("Total events: " . $data['summary']['total_events']);
                    $this->line("Scheduled: " . $data['summary']['scheduled']);
                    $this->line("In progress: " . $data['summary']['in_progress']);
                }
                
            } else {
                $this->error("API call failed: " . ($data['message'] ?? 'Unknown error'));
            }
            
        } catch (\Exception $e) {
            $this->error("Exception calling API: " . $e->getMessage());
            $this->line("Stack trace: " . $e->getTraceAsString());
        }
        
        return 0;
    }
}