<?php

namespace App\Actions\Favorite;

use App\Models\User;
use App\Services\FavoriteService;
use Illuminate\Database\Eloquent\Collection;

class ListFavoritesAction
{
    public function __construct(
        private readonly FavoriteService $favoriteService,
    ) {}

    public function execute(User $user): Collection
    {
        return $this->favoriteService->getUserFavorites($user);
    }
}