<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'description' => $this->description,
            'address' => $this->address,
            'scheduled_date' => $this->scheduled_date?->format('Y-m-d'),
            'scheduled_time' => $this->scheduled_time ? date('H:i', strtotime($this->scheduled_time)) : null,
            'completed_date' => $this->completed_date?->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'cost' => $this->cost,
            'notes' => $this->notes,
            'images' => $this->images,
            'next_service_date' => $this->next_service_date?->format('Y-m-d'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Additional fields
            'has_review' => $this->reviews->count() > 0,
            
            // Relationships
            'client' => new UserResource($this->whenLoaded('user')),
            'technician' => new UserResource($this->whenLoaded('technician')),
            'certificates' => CertificateResource::collection($this->whenLoaded('certificates')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}