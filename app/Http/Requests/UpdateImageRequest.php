<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Profile image is required',
            'image.image' => 'File must be an image',
            'image.mimes' => 'Image must be JPEG, PNG, JPG or GIF',
            'image.max' => 'Image size must not exceed 2MB',
        ];
    }
}
