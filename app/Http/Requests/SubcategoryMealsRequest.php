<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubcategoryMealsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'featured' => [
                'sometimes',
                'boolean',
            ],

            'in_stock' => [
                'sometimes',
                'boolean',
            ],

            'sort_by' => [
                'sometimes',
                'string',
                Rule::in([
                    'created_at',
                    'price',
                    'rating',
                    'title',
                    'sold_count',
                    'newest',
                ]),
            ],

            'sort_order' => [
                'sometimes',
                'string',
                Rule::in([
                    'asc',
                    'desc',
                ]),
            ],

            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:50',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('sort_order')) {
            $this->merge([
                'sort_order' => strtolower(
                    (string) $this->input('sort_order')
                ),
            ]);
        }
    }

    public function perPage(): int
    {
        return (int) $this->input('per_page', 15);
    }

    public function sortBy(): string
    {
        return $this->input('sort_by', 'created_at');
    }

    public function sortOrder(): string
    {
        return $this->input('sort_order', 'desc');
    }
}