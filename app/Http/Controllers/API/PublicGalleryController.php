<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Http\Resources\GalleryResource;
use Illuminate\Http\Request;

class PublicGalleryController extends Controller
{
    public function index(Request $request)
    {
        $query = Gallery::with(['media', 'thumbnail'])->active()->orderBy('created_at', 'desc');

        if ($request->has('category') && $request->category !== 'all') {
            $query->byCategory($request->category);
        }

        // Pagination support
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);
        $perPage = min($perPage, 50); // Max 50 items per page

        $galleries = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => GalleryResource::collection($galleries->items()),
            'pagination' => [
                'current_page' => $galleries->currentPage(),
                'last_page' => $galleries->lastPage(),
                'per_page' => $galleries->perPage(),
                'total' => $galleries->total(),
                'has_more' => $galleries->hasMorePages()
            ]
        ];
    }

    public function show($id)
    {
        $gallery = Gallery::active()->findOrFail($id);
        return new GalleryResource($gallery);
    }

    public function getFeatured(Request $request)
    {
        $limit = $request->get('limit', 6);
        $limit = min($limit, 10); // Max 10 featured items

        $featured = Gallery::active()
            ->featured()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return ['data' => GalleryResource::collection($featured)];
    }

    public function getFeaturedRandom(Request $request)
    {
        $limit = $request->get('limit', 4);
        $limit = min($limit, 10); // Max 10 random featured items

        $featured = Gallery::with(['media', 'thumbnail'])
            ->active()
            ->featured()
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return ['data' => GalleryResource::collection($featured)];
    }

    public function getCategories()
    {
        $categories = Gallery::active()
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return response()->json([
            'categories' => $categories
        ]);
    }
}