<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Actions\Category\GetCategoriesAction;
use App\Http\Resources\V1\CategoryResource;
use App\Actions\Category\GetCategoryAction;
use App\Actions\Category\GetCategoryMealsAction;
use App\Http\Resources\V1\MealResource;
class CategoryController extends Controller
{
    /**
     * Get all categories
     */
  public function index(
    Request $request,
    GetCategoriesAction $action
): JsonResponse {
    try {
        $categories = $action->execute();
return response()->json([
    'success' => true,
    'message' => 'Categories retrieved successfully',
    'data' => CategoryResource::collection($categories),
]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve categories',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    /**
     * Get single category with meals
     */
  public function show(
    string $id,
    GetCategoryAction $action
): JsonResponse {
    try {
        $category = $action->execute($id);

        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully',
            'data' => new CategoryResource($category),
        ]);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Category not found',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve category',
            'error' => $e->getMessage(),
        ], 500);
    }
}
    /**
     * Get meals by category (paginated)
     */
 public function meals(
    string $id,
    Request $request,
    GetCategoryMealsAction $action
): JsonResponse {
    try {
        $category = Category::findOrFail($id);

        $paginator = $action->execute(
            $category,
            $request
        );

        $total = $paginator->total();

        return response()->json(array_merge([
            'success' => true,
            'message' => $total === 0
                ? 'No products match your filters.'
                : 'Meals retrieved successfully',
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
               'meals' => MealResource::collection(
    $paginator->getCollection()
),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $total,
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
        ], $total === 0 ? [
            'empty_message' => 'No products match the applied filters. Try adjusting your filters.',
        ] : []));

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Category not found',
        ], 404);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve meals',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}