<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'certificate_number' => $this->certificate_number,
            'file_name' => $this->file_name,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'valid_until' => $this->valid_until?->format('Y-m-d'),
            'type' => $this->type,
            'status' => $this->status,
            'is_valid' => $this->isValid(),
            'is_expiring_soon' => $this->isExpiringSoon(),
            'download_url' => route('api.client.certificates.download', $this->id),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'client' => new UserResource($this->whenLoaded('user')),
            'service' => new ServiceResource($this->whenLoaded('service')),
            'issuer' => new UserResource($this->whenLoaded('issuer')),
        ];
    }
}