<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Gallery extends Model
{
    use HasFactory;

    protected $table = 'gallery';

    protected $fillable = [
        'title',
        'description',
        'image_url',
        'video_url',
        'media_type',
        'category',
        'is_active',
        'featured'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'featured' => 'boolean'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function media()
    {
        return $this->hasMany(GalleryMedia::class)->orderBy('order_index');
    }

    public function thumbnail()
    {
        return $this->hasOne(GalleryMedia::class)->where('is_thumbnail', true);
    }

    public function getMainImageAttribute()
    {
        // Return thumbnail if exists, otherwise fallback to image_url
        return $this->thumbnail?->media_url ?? $this->image_url;
    }
}
