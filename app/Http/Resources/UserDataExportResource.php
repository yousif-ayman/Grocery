<?php

namespace App\Http\Resources\DataExport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDataExportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'exported_at' => now()->toIso8601String(),

            'profile' => [
                'id' => $this->id,
                'username' => $this->username,
                'firstname' => $this->firstname,
                'lastname' => $this->lastname,
                'email' => $this->email,
                'phone' => $this->phone,
                'app_language' => $this->app_language,
                'app_theme' => $this->app_theme,
                'loyalty_points' => $this->loyalty_points,
                'store_credits' => (float) $this->store_credits,
                'created_at' => $this->created_at?->toIso8601String(),
            ],

            'addresses' => $this->addresses
                ->map(fn ($address) => [
                    'label' => $address->label,
                    'full_name' => $address->full_name,
                    'phone' => $address->phone,
                    'street_address' => $address->street_address,
                    'city' => $address->city,
                    'country' => $address->country,
                    'is_default' => (bool) $address->is_default,
                ])
                ->values()
                ->all(),

            'orders' => $this->orders
                ->map(fn ($order) => [
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total' => (float) $order->total,
                    'placed_at' => $order->placed_at?->toIso8601String(),
                ])
                ->values()
                ->all(),

            'notification_preferences' => [
                'order_updates' =>
                    (bool) $this->notificationSettings?->order_updates,

                'promotion_emails' =>
                    (bool) $this->notificationSettings?->promotion_emails,

                'nutrition_insights' =>
                    (bool) $this->notificationSettings?->nutrition_insights,

                'price_alerts' =>
                    (bool) $this->notificationSettings?->price_alerts,
            ],
        ];
    }
}