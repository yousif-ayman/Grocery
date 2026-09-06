<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactMessageStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    'new',
                    'read',
                    'replied',
                    'spam',
                ]),
            ],

            'admin_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}