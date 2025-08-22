<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GalleryMedia extends Model
{
    use HasFactory;

    protected $table = 'gallery_media';

    protected $fillable = [
        'gallery_id',
        'media_url',
        'media_type',
        'order_index',
        'is_thumbnail'
    ];

    protected $casts = [
        'is_thumbnail' => 'boolean'
    ];

    public function gallery()
    {
        return $this->belongsTo(Gallery::class);
    }

    public function scopeOrderedMedia($query)
    {
        return $query->orderBy('order_index');
    }

    public function scopeThumbnails($query)
    {
        return $query->where('is_thumbnail', true);
    }

    public function scopeImages($query)
    {
        return $query->where('media_type', 'image');
    }

    public function scopeVideos($query)
    {
        return $query->where('media_type', 'video');
    }
}