<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactMessageIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'nullable',
                Rule::in([
                    'new',
                    'read',
                    'replied',
                    'spam',
                ]),
            ],

            'from_date' => [
                'nullable',
                'date',
            ],

            'to_date' => [
                'nullable',
                'date',
                'after_or_equal:from_date',
            ],

            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sort_by' => [
                'nullable',
                Rule::in([
                    'created_at',
                    'name',
                    'email',
                    'status',
                ]),
            ],

            'sort_direction' => [
                'nullable',
                Rule::in([
                    'asc',
                    'desc',
                ]),
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }
}