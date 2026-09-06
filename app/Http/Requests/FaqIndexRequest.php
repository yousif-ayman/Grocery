<?php

namespace App\Http\Requests\Faq;

use Illuminate\Foundation\Http\FormRequest;

class FaqIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'max:100'],
            'active_only' => ['sometimes', 'boolean'],
            'search' => ['nullable', 'string', 'max:255'],
            'with_categories' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'active_only' => $this->boolean('active_only', true),
            'with_categories' => $this->boolean('with_categories', false),
            'per_page' => $this->integer('per_page', 15),
        ]);
    }
}