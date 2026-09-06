<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;
use App\Services\SmartListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmartListController extends Controller
{
    public function __construct(
        private SmartListService $smartListService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $smartLists = $this->smartListService->getUserSmartLists($request->user()->id);
        return $this->successResponse(SmartListResource::collection($smartLists), 'Smart lists retrieved successfully');
    }

    public function store(SmartListRequest $request): JsonResponse
    {
        $smartList = $this->smartListService->createSmartList(
            $request->user()->id,
            $request->validated(),
            $request->file('image')
        );

        return $this->successResponse(new SmartListResource($smartList), 'Smart list created successfully', 201);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $smartList = $this->smartListService->getSmartListById($request->user()->id, $id);
        return $this->successResponse(new SmartListResource($smartList), 'Smart list retrieved successfully');
    }

    public function update(SmartListRequest $request, $id): JsonResponse
    {
        $smartList = $this->smartListService->getSmartListById($request->user()->id, $id);
        $smartList = $this->smartListService->updateSmartList($smartList, $request->validated(), $request->file('image'));

        return $this->successResponse(new SmartListResource($smartList), 'Smart list updated successfully');
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $smartList = $this->smartListService->getSmartListById($request->user()->id, $id);
        $this->smartListService->deleteSmartList($smartList);

        return $this->successResponse(null, 'Smart list deleted successfully');
    }

    public function addMeal(Request $request, string $id): JsonResponse
    {
        $request->validate(['meal_id' => ['required', 'exists:meals,id']]);
        $smartList = $this->smartListService->getSmartListById($request->user()->id, $id);
        $smartList = $this->smartListService->addMealToList($smartList, $request->meal_id);

        return $this->successResponse(new SmartListResource($smartList), 'Item added to smart list successfully');
    }

    public function removeMeal(Request $request, string $id, string $mealId): JsonResponse
    {
        $smartList = $this->smartListService->getSmartListById($request->user()->id, $id);
        $smartList = $this->smartListService->removeMealFromList($smartList, $mealId);

        return $this->successResponse(new SmartListResource($smartList), 'Item removed from smart list successfully');
    }
}
