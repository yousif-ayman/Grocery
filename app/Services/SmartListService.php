<?php

namespace App\Services;

use App\Models\SmartList;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SmartListService
{
    public function getUserSmartLists(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return SmartList::where('user_id', $userId)->with('meals')->get();
    }

    public function createSmartList(int $userId, array $data, ?UploadedFile $image = null): SmartList
    {
        $data['user_id'] = $userId;
        $data['description'] = $data['description'] ?? '';
        $mealIds = $data['meal_ids'] ?? [];
        unset($data['meal_ids']);

        if ($image) {
            $data['image'] = $image->store('smart-lists', 'public');
        }

        $smartList = SmartList::create($data);

        if (!empty($mealIds)) {
            $smartList->meals()->attach($mealIds);
        }

        $smartList->load('meals');

        return $smartList;
    }

    public function getSmartListById(int $userId, int $id): SmartList
    {
        return SmartList::where('user_id', $userId)->with('meals')->findOrFail($id);
    }

    public function updateSmartList(SmartList $smartList, array $data, ?UploadedFile $image = null): SmartList
    {
        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }

        $mealIds = $data['meal_ids'] ?? null;
        unset($data['meal_ids']);

        if ($image) {
            if ($smartList->image && Storage::disk('public')->exists($smartList->image)) {
                Storage::disk('public')->delete($smartList->image);
            }
            $data['image'] = $image->store('smart-lists', 'public');
        }

        $smartList->update($data);

        if ($mealIds !== null) {
            $smartList->meals()->sync($mealIds);
        }

        $smartList->load('meals');

        return $smartList;
    }

    public function deleteSmartList(SmartList $smartList): bool
    {
        if ($smartList->image && Storage::disk('public')->exists($smartList->image)) {
            Storage::disk('public')->delete($smartList->image);
        }

        $smartList->meals()->detach();
        return $smartList->delete();
    }

    public function addMealToList(SmartList $smartList, int $mealId): SmartList
    {
        $smartList->meals()->syncWithoutDetaching([$mealId]);
        $smartList->load('meals');
        return $smartList;
    }

    public function removeMealFromList(SmartList $smartList, int $mealId): SmartList
    {
        $smartList->meals()->detach($mealId);
        $smartList->load('meals');
        return $smartList;
    }
}
