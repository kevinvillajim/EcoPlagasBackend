<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Http\Resources\ReviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicReviewController extends Controller
{
    /**
     * Get public reviews with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Review::with(['user', 'service', 'responder'])
                ->approved() // Only approved reviews
                ->public()   // Only public reviews
                ->verified() // Only verified reviews
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->has('rating') && $request->rating !== 'all') {
                $query->where('rating', $request->rating);
            }

            if ($request->has('service') && $request->service !== 'all') {
                $query->whereHas('service', function ($q) use ($request) {
                    $q->where('type', 'like', "%{$request->service}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'recent');
            switch ($sortBy) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'rating':
                    $query->orderBy('rating', 'desc');
                    break;
                case 'helpful':
                    $query->orderBy('helpful_count', 'desc');
                    break;
                case 'recent':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            $reviews = $query->paginate($request->get('per_page', 12));

            return response()->json([
                'success' => true,
                'data' => ReviewResource::collection($reviews),
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las reseñas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get featured reviews for homepage
     */
    public function getFeatured(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 3);
            
            $reviews = Review::with(['user', 'service'])
                ->approved()
                ->public()
                ->verified()
                ->featured()
                ->orderBy('helpful_count', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => ReviewResource::collection($reviews)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar reseñas destacadas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get review statistics for public display
     */
    public function getStats(): JsonResponse
    {
        try {
            $approvedReviews = Review::approved()->public()->verified();
            
            $totalReviews = $approvedReviews->count();
            $avgRating = $approvedReviews->avg('rating');
            
            $stats = [
                'total_reviews' => $totalReviews,
                'average_rating' => $avgRating ? round($avgRating, 1) : 0,
                'satisfaction' => $totalReviews > 0 ? round(($approvedReviews->where('rating', '>=', 4)->count() / $totalReviews) * 100) : 0,
                'five_stars' => $approvedReviews->where('rating', 5)->count(),
                'four_stars' => $approvedReviews->where('rating', 4)->count(),
                'three_stars' => $approvedReviews->where('rating', 3)->count(),
                'two_stars' => $approvedReviews->where('rating', 2)->count(),
                'one_star' => $approvedReviews->where('rating', 1)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available service types for filtering
     */
    public function getServiceTypes(): JsonResponse
    {
        try {
            $serviceTypes = Review::whereIn('reviews.status', ['approved', 'auto_approved'])
                ->where('reviews.is_public', true)
                ->where('reviews.verified', true)
                ->join('services', 'reviews.service_id', '=', 'services.id')
                ->distinct()
                ->pluck('services.type')
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $serviceTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar tipos de servicio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a review as helpful (public endpoint)
     */
    public function markHelpful($id): JsonResponse
    {
        try {
            $review = Review::approved()
                ->public()
                ->verified()
                ->findOrFail($id);

            $review->incrementHelpful();

            return response()->json([
                'success' => true,
                'message' => 'Marcado como útil',
                'helpful_count' => $review->helpful_count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como útil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific review by ID
     */
    public function show($id): JsonResponse
    {
        try {
            $review = Review::with(['user', 'service', 'responder'])
                ->approved()
                ->public()
                ->verified()
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new ReviewResource($review)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reseña no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
