<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image_url' => $this->image_url ? $this->formatMediaUrl($this->image_url) : null,
            'video_url' => $this->video_url,
            'media_type' => $this->media_type,
            'main_image' => $this->main_image ? $this->formatMediaUrl($this->main_image) : null,
            'media' => $this->media->map(function($media) {
                return [
                    'id' => $media->id,
                    'media_url' => $this->formatMediaUrl($media->media_url),
                    'media_type' => $media->media_type,
                    'is_thumbnail' => $media->is_thumbnail,
                    'order_index' => $media->order_index
                ];
            }),
            'category' => $this->category,
            'is_active' => $this->is_active,
            'featured' => $this->featured,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    private function formatMediaUrl($url)
    {
        if (!$url) return null;
        
        // If it's already a full URL, return as is (but check if it should have fallback)
        if (str_starts_with($url, 'http')) {
            return $url;
        }
        
        // If it starts with /storage, it's already correct for url() helper
        if (str_starts_with($url, '/storage')) {
            return url($url);
        }
        
        // If it starts with ./ (public folder), remove ./ and make absolute to backend
        if (str_starts_with($url, './')) {
            $filename = substr($url, 2);
            return url($filename);
        }
        
        // Check if it's a legacy asset filename (without timestamp prefix)
        if (preg_match('/^[a-zA-Z0-9_-]+\.(jpg|jpeg|png|gif|webp|mp4|avi|mov|wmv)$/i', $url)) {
            // For legacy assets, try backend first but frontend can implement fallback
            return url('/storage/gallery/' . $url);
        }
        
        // For all other cases (including gallery uploads), assume it's in storage/gallery
        return url('/storage/gallery/' . $url);
    }
}
