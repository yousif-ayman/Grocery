<?php

namespace App\Actions\Favorite;

use App\Models\Meal;
use App\Models\User;
use App\Services\FavoriteService;

class CheckFavoriteAction
{
    public function __construct(
        private readonly FavoriteService $favoriteService,
    ) {}

    public function execute(User $user, Meal $meal): bool
    {
        return $this->favoriteService->isFavorited(
            $user,
            $meal
        );
    }
}