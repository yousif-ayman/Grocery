<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubcategoryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'sometimes',
                'integer',
                'exists:categories,id',
            ],
        ];
    }

    public function categoryId(): ?int
    {
        return $this->has('category_id')
            ? (int) $this->input('category_id')
            : null;
    }
}