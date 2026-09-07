<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $identifier = $this->input('identifier')
            ?? $this->input('email')
            ?? $this->input('phone');

        $otp = $this->input('otp');

        $this->merge([
            'identifier' => is_string($identifier)
                ? trim($identifier)
                : $identifier,

            'otp' => is_string($otp)
                ? preg_replace('/\D/', '', $otp)
                : $otp,
        ]);
    }

    public function rules(): array
    {
        $otpLength = (int) config('otp.length', 4);

        return [
            'identifier' => [
                'required',
                'string',
            ],

            'otp' => [
                'required',
                'string',
                "size:{$otpLength}",
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.required' => 'Email or phone number is required.',
            'otp.required' => 'OTP is required.',
            'otp.size' => 'OTP must be :size digits.',
        ];
    }
}