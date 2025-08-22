<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryMedia;
use App\Http\Resources\GalleryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class GalleryManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Gallery::query();

        if ($request->has('category') && $request->category !== 'all') {
            $query->byCategory($request->category);
        }

        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $galleries = $query->with('media')->orderBy('created_at', 'desc')->get();
        return GalleryResource::collection($galleries);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'is_active' => 'nullable',
            'featured' => 'nullable',
            'media_files' => 'required|array|min:1',
            'media_files.*' => 'file|mimes:jpeg,png,jpg,gif,webp,mp4,avi,mov,wmv|max:51200',
            'thumbnail_index' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            \Log::error('Gallery validation failed:', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $mediaFiles = $request->file('media_files');
            $thumbnailIndex = $request->thumbnail_index ?? 0;
            $uploadedUrls = [];

            \Log::info('Gallery creation start:', [
                'files_count' => count($mediaFiles),
                'thumbnail_index' => $thumbnailIndex
            ]);

            // Upload all media files first
            foreach ($mediaFiles as $index => $file) {
                $mediaUrl = $this->uploadMedia($file);
                $uploadedUrls[] = $mediaUrl;
                \Log::info('Uploaded file:', ['index' => $index, 'url' => $mediaUrl]);
            }

            // Determine main media type
            $mainMediaType = $this->getMediaType($mediaFiles[$thumbnailIndex] ?? $mediaFiles[0]);

            // Create gallery item with thumbnail as main image
            $gallery = Gallery::create([
                'title' => $request->title,
                'description' => $request->description ?? '',
                'image_url' => $uploadedUrls[$thumbnailIndex] ?? $uploadedUrls[0],
                'media_type' => $mainMediaType,
                'category' => $request->category,
                'is_active' => ($request->is_active === '1' || $request->is_active === 'true') ? true : false,
                'featured' => ($request->featured === '1' || $request->featured === 'true') ? true : false
            ]);

            // Create media records
            foreach ($mediaFiles as $index => $file) {
                $mediaType = $this->getMediaType($file);
                
                $mediaRecord = GalleryMedia::create([
                    'gallery_id' => $gallery->id,
                    'media_url' => $uploadedUrls[$index],
                    'media_type' => $mediaType,
                    'order_index' => $index,
                    'is_thumbnail' => $index === $thumbnailIndex
                ]);
                
                \Log::info('Created media record:', [
                    'media_id' => $mediaRecord->id,
                    'gallery_id' => $gallery->id,
                    'media_url' => $uploadedUrls[$index],
                    'is_thumbnail' => $index === $thumbnailIndex
                ]);
            }

            DB::commit();
            return new GalleryResource($gallery->fresh(['media']));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gallery creation error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Error creating gallery item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $gallery = Gallery::findOrFail($id);
        return new GalleryResource($gallery);
    }

    public function update(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'is_active' => 'nullable|in:0,1,true,false',
            'featured' => 'nullable|in:0,1,true,false',
            'media_files' => 'nullable|array',
            'media_files.*' => 'file|mimes:jpeg,png,jpg,gif,webp,mp4,avi,mov,wmv|max:51200',
            'thumbnail_index' => 'nullable|integer|min:0',
            'replace_media' => 'boolean'
        ]);

        if ($validator->fails()) {
            \Log::error('Gallery validation failed:', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $updateData = [
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'is_active' => filter_var($request->is_active ?? $gallery->is_active, FILTER_VALIDATE_BOOLEAN),
                'featured' => filter_var($request->featured ?? $gallery->featured, FILTER_VALIDATE_BOOLEAN)
            ];

            // Handle media files if provided
            if ($request->hasFile('media_files')) {
                $replaceMedia = $request->replace_media ?? false;
                
                if ($replaceMedia) {
                    // Delete all existing media
                    foreach ($gallery->media as $media) {
                        $this->deleteMedia($media->media_url);
                        $media->delete();
                    }
                }

                // Upload new media files
                $mediaFiles = $request->file('media_files');
                $thumbnailIndex = $request->thumbnail_index ?? 0;
                $startIndex = $replaceMedia ? 0 : $gallery->media()->count();
                
                foreach ($mediaFiles as $index => $file) {
                    $mediaUrl = $this->uploadMedia($file);
                    $mediaType = $this->getMediaType($file);
                    
                    GalleryMedia::create([
                        'gallery_id' => $gallery->id,
                        'media_url' => $mediaUrl,
                        'media_type' => $mediaType,
                        'order_index' => $startIndex + $index,
                        'is_thumbnail' => $index === $thumbnailIndex && $replaceMedia
                    ]);
                }

                // Update main image_url from thumbnail
                $thumbnail = $gallery->media()->where('is_thumbnail', true)->first();
                if ($thumbnail) {
                    $updateData['image_url'] = $thumbnail->media_url;
                }
            }

            $gallery->update($updateData);

            DB::commit();
            return new GalleryResource($gallery->fresh(['media']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating gallery item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $gallery = Gallery::findOrFail($id);
            
            // Delete all media files
            foreach ($gallery->media as $media) {
                $this->deleteMedia($media->media_url);
            }
            
            // Delete main image if exists (for backward compatibility)
            if ($gallery->image_url) {
                $this->deleteMedia($gallery->image_url);
            }
            
            $gallery->delete();

            DB::commit();
            return response()->json([
                'message' => 'Gallery item deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error deleting gallery item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $gallery = Gallery::findOrFail($id);
            $gallery->update(['is_active' => !$gallery->is_active]);

            return new GalleryResource($gallery->fresh());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error toggling gallery status',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    private function uploadMedia($file)
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('gallery', $filename, 'public');
        return Storage::url($path);
    }

    private function getMediaType($file)
    {
        $mimeType = $file->getMimeType();
        
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        
        return 'image'; // default fallback
    }

    private function deleteMedia($mediaUrl)
    {
        if ($mediaUrl && Storage::disk('public')->exists(str_replace('/storage/', '', $mediaUrl))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $mediaUrl));
        }
    }

    // Backward compatibility methods
    private function uploadImage($image)
    {
        return $this->uploadMedia($image);
    }

    private function deleteImage($imageUrl)
    {
        $this->deleteMedia($imageUrl);
    }

    public function deleteMediaItem($galleryId, $mediaId)
    {
        try {
            $gallery = Gallery::findOrFail($galleryId);
            $media = $gallery->media()->findOrFail($mediaId);
            
            $this->deleteMedia($media->media_url);
            $media->delete();

            // Update thumbnail if deleted media was thumbnail
            if ($media->is_thumbnail) {
                $newThumbnail = $gallery->media()->first();
                if ($newThumbnail) {
                    $newThumbnail->update(['is_thumbnail' => true]);
                    $gallery->update(['image_url' => $newThumbnail->media_url]);
                }
            }

            return response()->json([
                'message' => 'Media item deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting media item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateMediaOrder(Request $request, $galleryId)
    {
        $validator = Validator::make($request->all(), [
            'media_order' => 'required|array',
            'media_order.*.id' => 'required|integer',
            'media_order.*.order_index' => 'required|integer',
            'thumbnail_id' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            \Log::error('Gallery validation failed:', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            $gallery = Gallery::findOrFail($galleryId);
            
            // Update media order
            foreach ($request->media_order as $mediaData) {
                $gallery->media()->where('id', $mediaData['id'])
                    ->update(['order_index' => $mediaData['order_index']]);
            }

            // Update thumbnail
            if ($request->thumbnail_id) {
                $gallery->media()->update(['is_thumbnail' => false]);
                $thumbnail = $gallery->media()->findOrFail($request->thumbnail_id);
                $thumbnail->update(['is_thumbnail' => true]);
                $gallery->update(['image_url' => $thumbnail->media_url]);
            }

            DB::commit();
            return new GalleryResource($gallery->fresh(['media']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating media order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}