<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'language' => [
                'required',
                'string',
                Rule::in(['en', 'ar']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'language.required' => 'Language is required.',
            'language.string' => 'Language must be a valid string.',
            'language.in' => 'Language must be either en or ar.',
        ];
    }
}