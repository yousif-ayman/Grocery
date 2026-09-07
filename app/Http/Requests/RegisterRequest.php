<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\UsernameMustContainLetter;
use App\Support\EgyptianPhoneRules;
use App\Support\EmailValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        $aliases = [
            'email' => ['Email'],
            'phone' => ['Phone', 'phone_number', 'phoneNumber'],
            'username' => ['Username', 'user_name', 'userName'],
        ];

        foreach ($aliases as $canonical => $keys) {
            if ($this->filled($canonical)) {
                continue;
            }

            foreach ($keys as $key) {
                if ($this->filled($key)) {
                    $merge[$canonical] = $this->input($key);
                    break;
                }
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }

        if ($this->has('phone') && is_string($this->input('phone'))) {
            $this->merge([
                'phone' => preg_replace(
                    '/\s+/',
                    '',
                    $this->input('phone')
                ),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:' . User::USERNAME_MAX_LENGTH,
                'not_regex:/\s/u',
                'alpha_dash',
                new UsernameMustContainLetter,
                'unique:users,username,NULL,id,deleted_at,NULL',
         
            ],

            'email' => [
                'nullable',
                'required_without:phone',
                ...EmailValidation::formatRules(),
                'max:255',
                'unique:users,email,NULL,id,deleted_at,NULL',
               
            ],

            'phone' => [
                'nullable',
                'string',
                'required_without:email',
                EgyptianPhoneRules::internationalPrefixRule(),
                'min:11',
                'max:13',
                EgyptianPhoneRules::mobileRule(),
                'unique:users,phone,NULL,id,deleted_at,NULL',
                
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)
                    ->letters()
                    ->numbers(),
                'max:20',
            ],

            'agree_terms' => [
                'required',
                'accepted',
            ],
        ];
    }

    public function messages(): array
    {
        $usernameMax = User::USERNAME_MAX_LENGTH;

        return [
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'username.max' => "Maximum {$usernameMax} characters allowed.",
            'username.not_regex' => 'Username must not contain spaces.',
            'username.alpha_dash' => 'Username may only contain letters, numbers, dashes and underscores.',

            'email.required_without' => 'Either email or phone number is required.',
            'email.email' => EmailValidation::invalidFormatMessage(),
            'email.regex' => EmailValidation::invalidFormatMessage(),
            'email.max' => 'The email address may not exceed 255 characters.',
            'email.not_regex' => EmailValidation::invalidFormatMessage(),
            'email.unique' => 'This email is already registered.',

            'phone.required_without' => 'Either phone number or email is required.',
            'phone.unique' => 'This phone number is already registered.',
            'phone.not_regex' => EgyptianPhoneRules::foreignPrefixMessage(),
            'phone.regex' => EgyptianPhoneRules::invalidMessage(),
            'phone.min' => EgyptianPhoneRules::lengthMessage(),
            'phone.max' => EgyptianPhoneRules::lengthMessage(),

            'password.confirmed' => 'Password confirmation does not match.',

            'agree_terms.required' => 'You must agree to the terms and conditions.',
            'agree_terms.accepted' => 'You must agree to the terms and conditions.',
        ];
    }
}