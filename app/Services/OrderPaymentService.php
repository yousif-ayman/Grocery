<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderPaymentService
{
    public function markOrderAsPaid(
        Order $order,
        string $checkoutSessionId,
        ?string $paymentIntentId
    ): Order {
        return DB::transaction(function () use (
            $order,
            $checkoutSessionId,
            $paymentIntentId
        ) {
            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            /*
             * Idempotency:
             * If the order was already processed,
             * do not process the payment again.
             */
            if ($order->payment_status === PaymentStatus::PAID) {
                return $order;
            }

            if ($order->status !== OrderStatus::AWAITING_PAYMENT) {
                return $order;
            }

            $order->update([
                'status' => OrderStatus::PLACED,
                'payment_status' => PaymentStatus::PAID,
                'placed_at' => now(),
                'stripe_payment_intent_id' => $paymentIntentId,
                'stripe_checkout_session_id' => $checkoutSessionId,
            ]);

            return $order->fresh();
        });
    }

    public function markPaymentAsFailed(
        Order $order,
        ?string $checkoutSessionId = null
    ): Order {
        return DB::transaction(function () use (
            $order,
            $checkoutSessionId
        ) {
            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($order->payment_status === PaymentStatus::PAID) {
                return $order;
            }

            $order->update([
                'payment_status' => PaymentStatus::FAILED,
                'stripe_checkout_session_id' =>
                    $checkoutSessionId
                    ?? $order->stripe_checkout_session_id,
            ]);

            return $order->fresh();
        });
    }
}