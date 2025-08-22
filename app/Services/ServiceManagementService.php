<?php

namespace App\Services;

use App\Models\Service;
use App\Models\User;

class ServiceManagementService
{
    /**
     * Get services for a specific client
     */
    public function getClientServices(User $client, array $filters = [])
    {
        $query = $client->services();
        
        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['type'])) {
            $query->where('type', 'like', '%' . $filters['type'] . '%');
        }
        
        return $query->with(['technician', 'certificates'])
                    ->orderBy('scheduled_date', 'desc')
                    ->paginate(15);
    }

    /**
     * Create new service
     */
    public function createService(array $serviceData)
    {
        $service = Service::create($serviceData);
        
        // TODO: Send notification to client and technician
        // TODO: Create calendar event
        
        return $service;
    }

    /**
     * Update service
     */
    public function updateService(Service $service, array $data)
    {
        $service->update($data);
        
        // TODO: Send notifications for status changes
        
        return $service;
    }

    /**
     * Assign technician to service
     */
    public function assignTechnician(Service $service, User $technician)
    {
        $service->update(['technician_id' => $technician->id]);
        
        // TODO: Send notification to technician
        
        return $service;
    }

    /**
     * Complete service
     */
    public function completeService(Service $service, array $completionData = [])
    {
        $service->update([
            'status' => Service::STATUS_COMPLETED,
            'completed_date' => now(),
            'notes' => $completionData['notes'] ?? $service->notes,
            'images' => $completionData['images'] ?? $service->images,
        ]);
        
        // TODO: Generate certificate
        // TODO: Send completion notification
        // TODO: Schedule next service if recurring
        
        return $service;
    }

    /**
     * Cancel service
     */
    public function cancelService(Service $service, string $reason = null)
    {
        $service->update([
            'status' => Service::STATUS_CANCELLED,
            'notes' => $reason ? "Cancelado: {$reason}" : 'Cancelado',
        ]);
        
        // TODO: Send cancellation notification
        
        return $service;
    }
}