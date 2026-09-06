<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubcategoryIndexRequest;
use App\Http\Requests\SubcategoryMealsRequest;
use App\Http\Resources\MealResource;
use App\Http\Resources\SubcategoryResource;
use App\Services\SubcategoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class SubcategoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SubcategoryService $subcategoryService
    ) {
    }

    /**
     * Get all active subcategories.
     */
    public function index(
        SubcategoryIndexRequest $request
    ): JsonResponse {
        try {
            $subcategories = $this->subcategoryService
                ->getSubcategories($request->categoryId());

            return $this->successResponse(
                SubcategoryResource::collection($subcategories),
                'Subcategories retrieved successfully'
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse(
                'Failed to retrieve subcategories.',
                500
            );
        }
    }

    /**
     * Get a single subcategory.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $subcategory = $this->subcategoryService
                ->getSubcategory($id);

            return $this->successResponse(
                new SubcategoryResource($subcategory),
                'Subcategory retrieved successfully'
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                'Subcategory not found.',
                404
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse(
                'Failed to retrieve subcategory.',
                500
            );
        }
    }

    /**
     * Get paginated meals for a subcategory.
     */
    public function meals(
        string $id,
        SubcategoryMealsRequest $request
    ): JsonResponse {
        try {
            $result = $this->subcategoryService
                ->getMeals($id, $request);

            $subcategory = $result['subcategory'];
            $paginator = $result['paginator'];

            $data = [
                'subcategory' => [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'slug' => $subcategory->slug,
                ],

                'meals' => MealResource::collection(
                    $paginator->getCollection()
                ),

                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ];

            if ($paginator->isEmpty()) {
                $data['empty_message'] =
                    'No products match the applied filters. Try adjusting your filters.';
            }

            return $this->successResponse(
                $data,
                $paginator->isEmpty()
                    ? 'No products match your filters.'
                    : 'Meals retrieved successfully'
            );
        } catch (ModelNotFoundException) {
            return $this->errorResponse(
                'Subcategory not found.',
                404
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse(
                'Failed to retrieve meals.',
                500
            );
        }
    }

    /**
     * Get lightweight subcategory information.
     */
    private function getSubcategorySummary(string $id): array
    {
        $subcategory = $this->subcategoryService
            ->getSubcategory($id);

        return [
            'id' => $subcategory->id,
            'name' => $subcategory->name,
            'slug' => $subcategory->slug,
        ];
    }
}