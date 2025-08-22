<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'response_message' => $this->response_message,
            'response_at' => $this->response_at?->format('Y-m-d H:i:s'),
            'is_public' => $this->is_public,
            
            // Moderation fields
            'status' => $this->status,
            'moderated_at' => $this->moderated_at?->format('Y-m-d H:i:s'),
            'is_auto_approved' => $this->is_auto_approved,
            
            // Additional fields
            'location' => $this->location,
            'verified' => $this->verified,
            'is_featured' => $this->is_featured,
            'helpful_count' => $this->helpful_count,
            
            // Status checks
            'has_response' => $this->hasResponse(),
            'is_approved' => $this->isApproved(),
            'is_pending' => $this->isPending(),
            'is_rejected' => $this->isRejected(),
            
            // Formatted dates
            'date' => $this->created_at->format('Y-m-d'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'client' => new UserResource($this->whenLoaded('user')),
            'service' => new ServiceResource($this->whenLoaded('service')),
            'responder' => new UserResource($this->whenLoaded('responder')),
            'moderator' => new UserResource($this->whenLoaded('moderator')),
        ];
    }
}