<?php

namespace App\Services;

use App\Http\Requests\SubcategoryMealsRequest;
use App\Models\Subcategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SubcategoryService
{
    private const SORTABLE_FIELDS = [
        'created_at',
        'rating',
        'title',
        'sold_count',
    ];

    public function getSubcategories(?int $categoryId = null)
    {
        $query = Subcategory::query()
            ->with('category')
            ->withCount([
                'meals as meals_count' => fn ($query) =>
                $query->available(),
            ])
            ->active();

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        return $query
            ->inRandomOrder()
            ->get();
    }

    public function getSubcategory(string $id): Subcategory
    {
        return Subcategory::query()
            ->with([
                'category',
                'meals' => fn ($query) =>
                $query
                    ->available()
                    ->limit(10),
            ])
            ->withCount([
                'meals as meals_count' => fn ($query) =>
                $query->available(),
            ])
            ->findOrFail($id);
    }

    public function getMeals(
        string $subcategoryId,
        SubcategoryMealsRequest $request
    ): array {
        $subcategory = Subcategory::query()
            ->select([
                'id',
                'name',
                'slug',
            ])
            ->findOrFail($subcategoryId);

        $query = $subcategory
            ->meals()
            ->available();

        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);

        $paginator = $query
            ->paginate($request->perPage())
            ->withQueryString();

        return [
            'subcategory' => $subcategory,
            'paginator' => $paginator,
        ];
    }

    private function applyFilters(
        Builder $query,
        SubcategoryMealsRequest $request
    ): void {
        if ($request->has('featured')) {
            $request->boolean('featured')
                ? $query->featured()
                : $query->where('is_featured', false);
        }

        if ($request->has('in_stock')) {
            $request->boolean('in_stock')
                ? $query->inStock()
                : $query->outOfStock();
        }
    }

    private function applySorting(
        Builder $query,
        SubcategoryMealsRequest $request
    ): void {
        $sortBy = $request->sortBy();
        $sortOrder = $request->sortOrder();

        if ($sortBy === 'newest') {
            $sortBy = 'created_at';
            $sortOrder = 'desc';
        }

        if ($sortBy === 'price') {
            $query->orderByRaw(
                'COALESCE(discount_price, price) ' .
                ($sortOrder === 'asc' ? 'ASC' : 'DESC')
            );

            return;
        }

        if (in_array($sortBy, self::SORTABLE_FIELDS, true)) {
            $query->orderBy($sortBy, $sortOrder);

            return;
        }

        $query->orderByDesc('created_at');
    }
}