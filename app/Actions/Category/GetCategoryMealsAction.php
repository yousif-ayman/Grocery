<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Http\Request;

class GetCategoryMealsAction
{
    public function execute(Category $category, Request $request)
    {
        $query = $category->meals()
            ->with(['subcategory'])
            ->available();

        // Filter by featured
        if ($request->has('featured')) {
            $request->boolean('featured')
                ? $query->featured()
                : $query->where('is_featured', false);
        }

        // Filter by subcategory
        if ($request->has('subcategory_id')) {
            $query->where(
                'subcategory_id',
                $request->input('subcategory_id')
            );
        }

        // Filter by stock
        if ($request->has('in_stock')) {
            $request->boolean('in_stock')
                ? $query->inStock()
                : $query->outOfStock();
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');

        $sortOrder = strtolower(
            $request->input('sort_order', 'desc')
        ) === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'newest') {
            $sortBy = 'created_at';
            $sortOrder = 'desc';
        }

        $allowedSortFields = [
            'created_at',
            'price',
            'rating',
            'title',
            'sold_count',
        ];

        if (in_array($sortBy, $allowedSortFields, true)) {
            if ($sortBy === 'price') {
                $query->orderByRaw(
                    'COALESCE(discount_price, price) ' . $sortOrder
                );
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = min(
            max((int) $request->input('per_page', 15), 1),
            50
        );

        return $query->paginate($perPage);
    }
}