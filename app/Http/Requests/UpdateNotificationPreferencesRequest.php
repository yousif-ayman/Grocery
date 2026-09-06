<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'order_updates' => [
                'sometimes',
                'boolean',
                'required_without_all:promotion_emails,nutrition_insights,price_alerts',
            ],

            'promotion_emails' => [
                'sometimes',
                'boolean',
                'required_without_all:order_updates,nutrition_insights,price_alerts',
            ],

            'nutrition_insights' => [
                'sometimes',
                'boolean',
                'required_without_all:order_updates,promotion_emails,price_alerts',
            ],

            'price_alerts' => [
                'sometimes',
                'boolean',
                'required_without_all:order_updates,promotion_emails,nutrition_insights',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'order_updates.boolean' => 'Order updates must be true or false.',
            'promotion_emails.boolean' => 'Promotion emails must be true or false.',
            'nutrition_insights.boolean' => 'Nutrition insights must be true or false.',
            'price_alerts.boolean' => 'Price alerts must be true or false.',

            'order_updates.required_without_all' =>
                'At least one notification preference must be provided.',
        ];
    }
}