<?php

namespace App\Services\Stripe;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StripeCustomerService
{
    public function __construct(
        private readonly StripeClient $stripeClient
    ) {}

    public function getOrCreateCustomer(
        User $user
    ): string {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        return DB::transaction(function () use ($user) {
            $user->refresh();

            if ($user->stripe_customer_id) {
                return $user->stripe_customer_id;
            }

            $customer = $this->stripeClient->createCustomer(
                [
                    'email' => $user->email,
                    'name' => $user->name,
                    'metadata' => [
                        'user_id' => (string) $user->id,
                    ],
                ],
                "create-customer-user-{$user->id}"
            );

            $updated = $user->update([
                'stripe_customer_id' => $customer->id,
            ]);

            if (!$updated) {
                throw new RuntimeException(
                    'Unable to save Stripe customer.'
                );
            }

            return $customer->id;
        });
    }
}