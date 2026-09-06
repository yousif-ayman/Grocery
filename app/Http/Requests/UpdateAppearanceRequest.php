<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'theme' => [
                'required',
                'string',
                Rule::in(['light', 'dark']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'theme.required' => 'Theme is required.',
            'theme.string' => 'Theme must be a valid string.',
            'theme.in' => 'Theme must be either light or dark.',
        ];
    }
}