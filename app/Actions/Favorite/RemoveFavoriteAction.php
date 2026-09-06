<?php

namespace App\Actions\Favorite;

use App\Models\Meal;
use App\Models\User;
use App\Services\FavoriteService;

class RemoveFavoriteAction
{
    public function __construct(
        private readonly FavoriteService $favoriteService,
    ) {}

    public function execute(User $user, Meal $meal): bool
    {
        return $this->favoriteService->remove(
            $user,
            $meal
        );
    }
}