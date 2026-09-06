<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\Stripe\StripeClient;
use DomainException;
use Stripe\Checkout\Session;

class StripeCheckoutService
{
    public function __construct(
        private readonly StripeClient $stripeClient,
        private readonly OrderPaymentService $orderPaymentService,
    ) {}

    /**
     * Create a Stripe Checkout Session for an order.
     */
    public function createSessionForOrder(
        Order $order,
        User $user
    ): Session {
        $this->ensureOrderCanBePaid($order);

        $amount = $this->calculatePayableAmount($order);

        $session = $this->stripeClient->createCheckoutSession([
            'mode' => 'payment',

            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',

                        'product_data' => [
                            'name' => "Order #{$order->order_number}",
                        ],

                        'unit_amount' => $this->toMinorUnits($amount),
                    ],

                    'quantity' => 1,
                ],
            ],

            'customer_email' => $user->email,

            'client_reference_id' => (string) $order->id,

            'metadata' => [
                'order_id' => (string) $order->id,
                'user_id' => (string) $user->id,
            ],

            'success_url' => config(
                    'services.stripe.success_url'
                ) . '?session_id={CHECKOUT_SESSION_ID}',

            'cancel_url' => config(
                'services.stripe.cancel_url'
            ),
        ]);

        $order->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        return $session;
    }

    /**
     * Verify a Stripe Checkout Session.
     */
    public function verifySession(
        string $sessionId,
        Order $order
    ): Order {
        $session = $this->stripeClient
            ->retrieveCheckoutSession($sessionId);

        $this->validateSessionOwnership(
            $session,
            $order
        );

        $this->ensurePaymentIsCompleted($session);

        $paymentIntentId = $this->extractPaymentIntentId(
            $session
        );

        return $this->orderPaymentService->markOrderAsPaid(
            $order,
            $session->id,
            $paymentIntentId
        );
    }

    /**
     * Process a Stripe webhook Checkout Session.
     */
    public function handleCompletedCheckoutSession(
        Session $session
    ): ?Order {
        $orderId = $this->extractOrderId($session);

        if (! $orderId) {
            return null;
        }

        $order = Order::query()->find($orderId);

        if (! $order) {
            return null;
        }

        $this->validateSessionOwnership(
            $session,
            $order
        );

        if ($session->payment_status !== 'paid') {
            return $order;
        }

        $paymentIntentId = $this->extractPaymentIntentId(
            $session
        );

        return $this->orderPaymentService->markOrderAsPaid(
            $order,
            $session->id,
            $paymentIntentId
        );
    }

    private function ensureOrderCanBePaid(
        Order $order
    ): void {
        if ($order->status !== OrderStatus::AWAITING_PAYMENT) {
            throw new DomainException(
                'This order is not available for payment.'
            );
        }
    }

    private function calculatePayableAmount(
        Order $order
    ): float {
        /*
         * IMPORTANT:
         * Never trust the amount coming from the client.
         *
         * Replace `total` with your actual final-total column
         * if your database uses another name.
         */
        return (float) $order->total;
    }

    private function toMinorUnits(
        float $amount
    ): int {
        return (int) round($amount * 100);
    }

    private function ensurePaymentIsCompleted(
        Session $session
    ): void {
        if ($session->payment_status !== 'paid') {
            throw new DomainException(
                'Payment has not been completed.'
            );
        }
    }

    private function extractOrderId(
        Session $session
    ): ?int {
        $orderId = $session->metadata->order_id
            ?? $session->client_reference_id
            ?? null;

        return $orderId
            ? (int) $orderId
            : null;
    }

    private function extractPaymentIntentId(
        Session $session
    ): ?string {
        $paymentIntent = $session->payment_intent;

        if (is_string($paymentIntent)) {
            return $paymentIntent;
        }

        return $paymentIntent->id ?? null;
    }

    private function validateSessionOwnership(
        Session $session,
        Order $order
    ): void {
        $sessionOrderId = $this->extractOrderId($session);

        if (
            $sessionOrderId === null ||
            $sessionOrderId !== (int) $order->id
        ) {
            throw new DomainException(
                'Payment session does not belong to this order.'
            );
        }
    }
}