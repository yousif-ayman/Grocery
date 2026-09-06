<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\Meal;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FavoriteService
{
    public function getUserFavorites(User $user): Collection
    {
        return $user->favorites()
            ->with([
                'meal.category',
                'meal.subcategory',
            ])
            ->latest()
            ->get();
    }

    public function toggle(User $user, Meal $meal): bool
    {
        return DB::transaction(function () use ($user, $meal): bool {
            $favorite = $user->favorites()
                ->where('meal_id', $meal->id)
                ->first();

            if ($favorite) {
                $favorite->delete();

                return false;
            }

            $user->favorites()->create([
                'meal_id' => $meal->id,
            ]);

            return true;
        });
    }

    public function isFavorited(User $user, Meal $meal): bool
    {
        return $user->favorites()
            ->where('meal_id', $meal->id)
            ->exists();
    }

    public function remove(User $user, Meal $meal): bool
    {
        return (bool) $user->favorites()
            ->where('meal_id', $meal->id)
            ->delete();
    }
}