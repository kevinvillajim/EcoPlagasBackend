<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Service;
use App\Http\Resources\ReviewResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Get client's reviews
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $reviews = Review::with(['service', 'responder', 'moderator'])
                ->where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 10));

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
     * Create a new review for a service
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de reseña inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verify the service belongs to the user and is completed
            $service = Service::where('id', $request->service_id)
                ->where('user_id', $request->user()->id)
                ->where('status', 'completed')
                ->first();

            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo puedes calificar servicios completados que hayas contratado'
                ], 403);
            }

            // Check if review already exists
            $existingReview = Review::where('user_id', $request->user()->id)
                ->where('service_id', $request->service_id)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya has calificado este servicio'
                ], 409);
            }

            // Determine initial status based on rating (auto-approval logic)
            $autoApprovalThreshold = 4; // This could come from settings
            $status = 'pending';
            $isAutoApproved = false;

            if ($request->rating >= $autoApprovalThreshold) {
                $status = 'auto_approved';
                $isAutoApproved = true;
            }

            $review = Review::create([
                'user_id' => $request->user()->id,
                'service_id' => $request->service_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'location' => $request->location,
                'status' => $status,
                'is_auto_approved' => $isAutoApproved,
                'moderated_at' => $isAutoApproved ? now() : null,
                'verified' => true, // Client reviews are considered verified
                'is_public' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => $isAutoApproved 
                    ? 'Reseña enviada y aprobada automáticamente' 
                    : 'Reseña enviada. Será revisada antes de publicarse',
                'data' => new ReviewResource($review->load(['service', 'responder', 'moderator']))
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la reseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing review (only if pending)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de reseña inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $review = Review::where('id', $id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reseña no encontrada'
                ], 404);
            }

            if (!$review->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo puedes editar reseñas pendientes'
                ], 403);
            }

            // Re-evaluate auto-approval
            $autoApprovalThreshold = 4;
            $status = 'pending';
            $isAutoApproved = false;

            if ($request->rating >= $autoApprovalThreshold) {
                $status = 'auto_approved';
                $isAutoApproved = true;
            }

            $review->update([
                'rating' => $request->rating,
                'comment' => $request->comment,
                'location' => $request->location,
                'status' => $status,
                'is_auto_approved' => $isAutoApproved,
                'moderated_at' => $isAutoApproved ? now() : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => $isAutoApproved 
                    ? 'Reseña actualizada y aprobada automáticamente' 
                    : 'Reseña actualizada. Será revisada antes de publicarse',
                'data' => new ReviewResource($review->load(['service', 'responder', 'moderator']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la reseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a review (only if pending)
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $review = Review::where('id', $id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reseña no encontrada'
                ], 404);
            }

            if (!$review->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo puedes eliminar reseñas pendientes'
                ], 403);
            }

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

    /**
     * Get services that can be reviewed by the client
     */
    public function getReviewableServices(Request $request): JsonResponse
    {
        try {
            $services = Service::where('user_id', $request->user()->id)
                ->where('status', 'completed')
                ->whereDoesntHave('reviews') // Services without reviews
                ->orderBy('updated_at', 'desc')
                ->get(['id', 'type', 'scheduled_date', 'address']);

            return response()->json([
                'success' => true,
                'data' => $services
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar servicios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a review as helpful (increment counter)
     */
    public function markHelpful(Request $request, $id): JsonResponse
    {
        try {
            $review = Review::approved()
                ->public()
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
}
