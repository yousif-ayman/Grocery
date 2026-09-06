<?php

namespace App\Services\Payment;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\Stripe\StripeClient;
use App\Services\Stripe\StripeCustomerService;
use DomainException;
use Stripe\Exception\ApiErrorException;

class SavedCardPaymentService
{
    public function __construct(
        private readonly StripeClient $stripeClient,
        private readonly StripeCustomerService $customerService,
    ) {}

    public function createSetupIntent(
        User $user
    ): array {
        $customerId =
            $this->customerService
                ->getOrCreateCustomer($user);

        $intent = $this->stripeClient
            ->createSetupIntent(
                [
                    'customer' => $customerId,

                    'payment_method_types' => [
                        'card',
                    ],

                    /*
                     * The card will be reused when
                     * the customer is not present.
                     */
                    'usage' => 'off_session',
                ],
                "setup-intent-user-{$user->id}-"
                . now()->format('YmdHi')
            );

        return [
            'client_secret' => $intent->client_secret,
            'setup_intent_id' => $intent->id,
        ];
    }

    public function listCards(
        User $user
    ): array {
        if (!$user->stripe_customer_id) {
            return [];
        }

        $cards = $this->stripeClient
            ->listCards(
                $user->stripe_customer_id
            );

        return array_map(
            fn ($card) => $this->transformCard($card),
            $cards
        );
    }

    public function deleteCard(
        User $user,
        string $paymentMethodId
    ): void {
        $customerId =
            $user->stripe_customer_id;

        if (!$customerId) {
            throw new DomainException(
                'No Stripe customer is associated with this account.'
            );
        }

        $paymentMethod = $this->stripeClient
            ->retrievePaymentMethod(
                $paymentMethodId
            );

        /*
         * Critical security check:
         * the PaymentMethod must belong to
         * the authenticated user's Stripe Customer.
         */
        if (
            $paymentMethod->customer !== $customerId
        ) {
            throw new DomainException(
                'Payment method does not belong to this account.'
            );
        }

        $this->stripeClient
            ->detachPaymentMethod(
                $paymentMethodId
            );
    }

    public function chargeSavedCard(
        User $user,
        Order $order,
        string $paymentMethodId
    ): array {
        $this->validateOrder($user, $order);

        $customerId =
            $this->customerService
                ->getOrCreateCustomer($user);

        $paymentMethod = $this->stripeClient
            ->retrievePaymentMethod(
                $paymentMethodId
            );

        /*
         * Never trust the PaymentMethod ID
         * supplied by the client.
         */
        if (
            $paymentMethod->customer !== $customerId
        ) {
            throw new DomainException(
                'Payment method does not belong to this account.'
            );
        }

        $amount = $this->calculateOrderAmount(
            $order
        );

        $paymentIntent =
            $this->stripeClient
                ->createPaymentIntent(
                    [
                        'amount' =>
                            $this->toMinorUnits($amount),

                        'currency' =>
                            config(
                                'services.stripe.currency'
                            ),

                        'customer' =>
                            $customerId,

                        'payment_method' =>
                            $paymentMethodId,

                        'off_session' => true,

                        'confirm' => true,

                        'metadata' => [
                            'order_id' =>
                                (string) $order->id,

                            'user_id' =>
                                (string) $user->id,
                        ],
                    ],

                    /*
                     * One logical payment attempt
                     * should have one idempotency key.
                     */
                    "order-payment-{$order->id}"
                );

        return [
            'payment_intent_id' =>
                $paymentIntent->id,

            'status' =>
                $paymentIntent->status,

            'amount' =>
                $paymentIntent->amount,

            'currency' =>
                $paymentIntent->currency,
        ];
    }

    private function validateOrder(
        User $user,
        Order $order
    ): void {
        if ($order->user_id !== $user->id) {
            throw new DomainException(
                'You are not allowed to pay for this order.'
            );
        }

        if (
            $order->status !==
            OrderStatus::AWAITING_PAYMENT
        ) {
            throw new DomainException(
                'This order is not available for payment.'
            );
        }

        if (
            $order->payment_status ===
            PaymentStatus::PAID
        ) {
            throw new DomainException(
                'This order has already been paid.'
            );
        }
    }

    private function calculateOrderAmount(
        Order $order
    ): float {
        /*
         * Replace this with your actual
         * order pricing/domain calculation.
         */
        return (float) $order->total;
    }

    private function toMinorUnits(
        float $amount
    ): int {
        return (int) round($amount * 100);
    }

    private function transformCard(
        object $card
    ): array {
        return [
            'id' => $card->id,
            'brand' => $card->card->brand ?? null,
            'last4' => $card->card->last4 ?? null,
            'exp_month' =>
                $card->card->exp_month ?? null,
            'exp_year' =>
                $card->card->exp_year ?? null,
        ];
    }
}