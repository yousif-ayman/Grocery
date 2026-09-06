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
        return response()->json([
            'success' => true, 'message' => 'Smart lists retrieved successfully',
            'data' => SmartListResource::collection($this->smartListService->getUserSmartLists($request->user()->id)),
        ]);
    }

    public function store(SmartListRequest $request): JsonResponse
    {
        $smartList = $this->smartListService->createSmartList(
            $request->user()->id,
            $request->validated(),
            $request->file('image')
        );

        return response()->json([
            'success' => true, 'message' => 'Smart list created successfully',
            'data' => new SmartListResource($smartList),
        ]);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $smartList = $this->smartListService->getSmartListById($request->user()->id, $id);

        return response()->json([
            'success' => true, 'message' => 'Smart list retrieved successfully',
            'data' => new SmartListResource($smartList),
        ]);
    }

    public function update(SmartListRequest $request, $id): JsonResponse
    {
        $smartList = $this->smartListService->getSmartListById($request->user()->id, $id);
        $smartList = $this->smartListService->updateSmartList($smartList, $request->validated(), $request->file('image'));

        return response()->json([
            'success' => true, 'message' => 'Smart list updated successfully',
            'data' => new SmartListResource($smartList),
        ]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $smartList = $this->smartListService->getSmartListById($request->user()->id, $id);
        $this->smartListService->deleteSmartList($smartList);

        return response()->json(['success' => true, 'message' => 'Smart list deleted successfully']);
    }

    public function addMeal(Request $request, string $id): JsonResponse
    {
        $request->validate(['meal_id' => ['required', 'exists:meals,id']]);
        $smartList = $this->smartListService->getSmartListById($request->user()->id, $id);
        $smartList = $this->smartListService->addMealToList($smartList, $request->meal_id);

        return response()->json([
            'success' => true, 'message' => 'Item added to smart list successfully',
            'data' => new SmartListResource($smartList),
        ]);
    }

    public function removeMeal(Request $request, string $id, string $mealId): JsonResponse
    {
        $smartList = $this->smartListService->getSmartListById($request->user()->id, $id);
        $smartList = $this->smartListService->removeMealFromList($smartList, $mealId);

        return response()->json([
            'success' => true, 'message' => 'Item removed from smart list successfully',
            'data' => new SmartListResource($smartList),
        ]);
    }
}
