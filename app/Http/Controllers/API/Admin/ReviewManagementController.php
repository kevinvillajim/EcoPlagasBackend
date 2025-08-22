<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Http\Resources\ReviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReviewManagementController extends Controller
{
    /**
     * Get all reviews with filters for admin dashboard
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Review::with(['user', 'service', 'moderator'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->has('status') && $request->status !== 'all') {
                $query->byStatus($request->status);
            }

            if ($request->has('rating') && $request->rating !== 'all') {
                $query->where('rating', $request->rating);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('service', function ($q) use ($search) {
                    $q->where('type', 'like', "%{$search}%");
                })->orWhere('comment', 'like', "%{$search}%");
            }

            $reviews = $query->paginate($request->get('per_page', 15));

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
     * Get review statistics for admin dashboard
     */
    public function getStats(): JsonResponse
    {
        try {
            $totalReviews = Review::count();
            $avgRating = Review::avg('rating');
            
            $stats = [
                'total' => $totalReviews,
                'approved' => Review::byStatus('approved')->count() + Review::byStatus('auto_approved')->count(),
                'pending' => Review::byStatus('pending')->count(),
                'rejected' => Review::byStatus('rejected')->count(),
                'auto_approved' => Review::byStatus('auto_approved')->count(),
                'average_rating' => $avgRating ? round($avgRating, 1) : 0,
                'five_stars' => Review::where('rating', 5)->count(),
                'four_stars' => Review::where('rating', 4)->count(),
                'three_stars' => Review::where('rating', 3)->count(),
                'two_stars' => Review::where('rating', 2)->count(),
                'one_star' => Review::where('rating', 1)->count(),
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
     * Approve a review
     */
    public function approve(Request $request, $id): JsonResponse
    {
        try {
            $review = Review::findOrFail($id);
            
            if (!$review->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden aprobar reseñas pendientes'
                ], 400);
            }

            $review->approve($request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Reseña aprobada exitosamente',
                'data' => new ReviewResource($review->load(['user', 'service', 'moderator']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar la reseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a review
     */
    public function reject(Request $request, $id): JsonResponse
    {
        try {
            $review = Review::findOrFail($id);
            
            if (!$review->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden rechazar reseñas pendientes'
                ], 400);
            }

            $review->reject($request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Reseña rechazada exitosamente',
                'data' => new ReviewResource($review->load(['user', 'service', 'moderator']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar la reseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle featured status of a review
     */
    public function toggleFeatured($id): JsonResponse
    {
        try {
            $review = Review::findOrFail($id);
            
            if (!$review->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden destacar reseñas aprobadas'
                ], 400);
            }

            $review->update(['is_featured' => !$review->is_featured]);

            return response()->json([
                'success' => true,
                'message' => $review->is_featured ? 'Reseña destacada' : 'Reseña removida de destacadas',
                'data' => new ReviewResource($review->load(['user', 'service', 'moderator']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado destacado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Respond to a review
     */
    public function respond(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'response_message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de respuesta inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $review = Review::findOrFail($id);
            
            if (!$review->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden responder reseñas aprobadas'
                ], 400);
            }

            $review->update([
                'response_message' => $request->response_message,
                'response_by' => $request->user()->id,
                'response_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Respuesta enviada exitosamente',
                'data' => new ReviewResource($review->load(['user', 'service', 'moderator', 'responder']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar respuesta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update moderation settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'auto_approval_threshold' => 'required|integer|min:1|max:5',
            'require_moderation' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Configuración inválida',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Save settings to config or database
            // For now, we'll return success and implement auto-approval logic
            
            // Auto-approve existing pending reviews based on new threshold
            if ($request->require_moderation) {
                $pendingReviews = Review::pending()
                    ->where('rating', '>=', $request->auto_approval_threshold)
                    ->get();

                foreach ($pendingReviews as $review) {
                    $review->autoApprove();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Configuración actualizada exitosamente',
                'data' => [
                    'auto_approval_threshold' => $request->auto_approval_threshold,
                    'require_moderation' => $request->require_moderation,
                    'auto_approved_count' => $pendingReviews->count() ?? 0
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar configuración',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a review
     */
    public function destroy($id): JsonResponse
    {
        try {
            $review = Review::findOrFail($id);
            $review->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reseña eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la reseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
