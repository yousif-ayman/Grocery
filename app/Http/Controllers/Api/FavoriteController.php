<?php

namespace App\Http\Controllers\Api;

use App\Actions\Favorite\CheckFavoriteAction;
use App\Actions\Favorite\ListFavoritesAction;
use App\Actions\Favorite\RemoveFavoriteAction;
use App\Actions\Favorite\ToggleFavoriteAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteMealResource;
use App\Models\Meal;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(
        private readonly ListFavoritesAction $listFavoritesAction,
        private readonly ToggleFavoriteAction $toggleFavoriteAction,
        private readonly CheckFavoriteAction $checkFavoriteAction,
        private readonly RemoveFavoriteAction $removeFavoriteAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $favorites = $this->listFavoritesAction->execute(
            $request->user()
        );

        return $this->success(
            data: FavoriteMealResource::collection($favorites),
            message: 'Favorites retrieved successfully'
        );
    }

    public function toggle(
        Request $request,
        string $mealId
    ): JsonResponse {
        $meal = Meal::findOrFail($mealId);

        $isFavorited = $this->toggleFavoriteAction->execute(
            $request->user(),
            $meal
        );

        return $this->success(
            data: [
                'meal_id' => $meal->id,
                'is_favorited' => $isFavorited,
            ],
            message: $isFavorited
                ? 'Added to favorites'
                : 'Removed from favorites'
        );
    }

    public function check(
        Request $request,
        string $mealId
    ): JsonResponse {
        $meal = Meal::findOrFail($mealId);

        $isFavorited = $this->checkFavoriteAction->execute(
            $request->user(),
            $meal
        );

        return $this->success(
            data: [
                'meal_id' => $meal->id,
                'is_favorited' => $isFavorited,
            ]
        );
    }

    public function remove(
        Request $request,
        string $mealId
    ): JsonResponse {
        $meal = Meal::findOrFail($mealId);

        $deleted = $this->removeFavoriteAction->execute(
            $request->user(),
            $meal
        );

        if (! $deleted) {
            return $this->error(
                message: 'Meal was not in favorites',
                status: 404
            );
        }

        return $this->success(
            data: [
                'meal_id' => $meal->id,
                'is_favorited' => false,
            ],
            message: 'Removed from favorites'
        );
    }
}