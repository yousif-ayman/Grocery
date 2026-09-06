<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewService
{
    public function getReviews(Request $request): object
    {
        $query = Review::query()->with(['user', 'meal'])->latest();

        if ($request->has('meal_id')) $query->where('meal_id', $request->meal_id);
        if ($request->has('user_id')) $query->where('user_id', $request->user_id);
        if ($request->has('rating')) $query->where('rating', $request->rating);
        if ($request->boolean('approved_only', true)) $query->approved();
        if ($request->has('min_rating')) $query->where('rating', '>=', $request->min_rating);

        return $query->paginate(min(max((int) $request->input('per_page', 15), 1), 50));
    }

    public function createReview(array $data): Review
    {
        return Review::create([
            'user_id' => Auth::id(),
            'meal_id' => $data['meal_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'images' => $data['images'] ?? null,
            'is_approved' => false,
        ]);
    }

    public function hasUserReviewed(int $userId, int $mealId): bool
    {
        return Review::hasUserReviewed($userId, $mealId);
    }

    public function getReviewById(int $id): Review
    {
        return Review::with(['user', 'meal'])->where('user_id', Auth::id())->findOrFail($id);
    }

    public function updateReview(Review $review, array $data): Review
    {
        $review->update($data);
        return $review;
    }

    public function deleteReview(Review $review): bool
    {
        return $review->delete();
    }

    public function getMealReviews(int $mealId, Request $request): array
    {
        $meal = Meal::findOrFail($mealId);
        $perPage = min(max((int) $request->input('per_page', 15), 1), 50);

        $reviews = Review::with('user')->where('meal_id', $mealId)
            ->approved()->latest()->paginate($perPage);

        return [
            'meal' => [
                'id' => $meal->id, 'name' => $meal->name,
                'average_rating' => round(Review::getAverageRating($mealId), 1),
                'total_reviews' => Review::getTotalReviews($mealId),
            ],
            'reviews' => $reviews,
        ];
    }

    public function getUserReviews(Request $request): object
    {
        return Review::with('meal')->where('user_id', Auth::id())
            ->latest()
            ->paginate(min(max((int) $request->input('per_page', 15), 1), 50));
    }

    public function getMealReviewStats(int $mealId): array
    {
        Meal::findOrFail($mealId);

        $stats = Review::where('meal_id', $mealId)->approved()
            ->selectRaw('COUNT(*) as total_reviews, AVG(rating) as average_rating,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star')
            ->first();

        return [
            'total_reviews' => (int) $stats->total_reviews,
            'average_rating' => round($stats->average_rating ?? 0, 1),
            'rating_distribution' => [
                'five_star' => (int) $stats->five_star, 'four_star' => (int) $stats->four_star,
                'three_star' => (int) $stats->three_star, 'two_star' => (int) $stats->two_star,
                'one_star' => (int) $stats->one_star,
            ]
        ];
    }

    public function canUserEditReview(Review $review, int $userId): bool
    {
        return $review->user_id === $userId;
    }
}
