<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupportReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'issue_type' => is_string($this->issue_type)
                ? trim($this->issue_type)
                : $this->issue_type,

            'order_number' => $this->filled('order_number')
                ? trim((string) $this->order_number)
                : null,

            'message' => is_string($this->message)
                ? trim($this->message)
                : $this->message,
        ]);
    }

    public function rules(): array
    {
        return [
            'issue_type' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'order_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::exists('orders', 'order_number')
                    ->where('user_id', $this->user()?->id),
            ],

            'message' => [
                'required',
                'string',
                'min:10',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'issue_type.required' => 'Issue type is required.',
            'issue_type.min' => 'Issue type must be at least 2 characters.',
            'issue_type.max' => 'Issue type may not exceed 255 characters.',

            'order_number.exists' => 'Order number not found on your account.',

            'message.required' => 'Message is required.',
            'message.min' => 'Message must be at least 10 characters.',
            'message.max' => 'Message may not exceed 2000 characters.',
        ];
    }
}