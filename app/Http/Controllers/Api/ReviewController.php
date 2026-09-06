<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $reviews = $this->reviewService->getReviews($request);

        return $this->successResponse([
            'data' => ReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(), 'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(), 'total' => $reviews->total(),
            ]
        ]);
    }

    public function store(StoreReviewRequest $request): JsonResponse
    {
        if ($this->reviewService->hasUserReviewed(Auth::id(), $request->meal_id)) {
            return $this->errorResponse('You have already reviewed this meal');
        }

        $review = $this->reviewService->createReview($request->validated());

        return $this->successResponse(
            new ReviewResource($review->load(['user', 'meal'])),
            'Review submitted successfully. Waiting for admin approval.',
            201
        );
    }

    public function show($id): JsonResponse
    {
        $review = $this->reviewService->getReviewById($id);
        return $this->successResponse(new ReviewResource($review));
    }

    public function update(UpdateReviewRequest $request, $id): JsonResponse
    {
        $review = Review::findOrFail($id);

        if (!$this->reviewService->canUserEditReview($review, Auth::id()) && !Auth::user()->is_admin) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $review = $this->reviewService->updateReview($review, $request->validated());

        return $this->successResponse(
            new ReviewResource($review->load(['user', 'meal'])),
            'Review updated successfully'
        );
    }

    public function destroy($id): JsonResponse
    {
        $review = Review::findOrFail($id);

        if (!$this->reviewService->canUserEditReview($review, Auth::id()) && !Auth::user()->is_admin) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $this->reviewService->deleteReview($review);
        return $this->successResponse(null, 'Review deleted successfully');
    }

    public function getMealReviews($mealId, Request $request): JsonResponse
    {
        $result = $this->reviewService->getMealReviews($mealId, $request);

        return $this->successResponse([
            'meal' => $result['meal'],
            'data' => ReviewResource::collection($result['reviews']),
            'meta' => [
                'current_page' => $result['reviews']->currentPage(), 'last_page' => $result['reviews']->lastPage(),
                'per_page' => $result['reviews']->perPage(), 'total' => $result['reviews']->total(),
            ]
        ]);
    }

    public function getUserReviews(Request $request): JsonResponse
    {
        $reviews = $this->reviewService->getUserReviews($request);

        return $this->successResponse([
            'data' => ReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(), 'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(), 'total' => $reviews->total(),
            ]
        ]);
    }

    public function getMealReviewStats($mealId): JsonResponse
    {
        return $this->successResponse($this->reviewService->getMealReviewStats($mealId));
    }
}
