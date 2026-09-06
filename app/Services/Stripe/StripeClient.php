<?php

namespace App\Services\Stripe;

use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class StripeClient
{
    public function __construct()
    {
        Stripe::setApiKey(
            config('services.stripe.secret')
        );
    }

    /**
     * @throws ApiErrorException
     */
    public function createCheckoutSession(
        array $parameters
    ): Session {
        return Session::create($parameters);
    }

    /**
     * @throws ApiErrorException
     */
    public function retrieveCheckoutSession(
        string $sessionId
    ): Session {
        return Session::retrieve($sessionId);
    }

    public function createCustomer(
        array $parameters,
        ?string $idempotencyKey = null
    ): Customer {
        return Customer::create(
            $parameters,
            $this->requestOptions($idempotencyKey)
        );
    }

    public function createSetupIntent(
        array $parameters,
        ?string $idempotencyKey = null
    ): SetupIntent {
        return SetupIntent::create(
            $parameters,
            $this->requestOptions($idempotencyKey)
        );
    }

    public function listCards(
        string $customerId
    ): array {
        $paymentMethods = PaymentMethod::all([
            'customer' => $customerId,
            'type' => 'card',
        ]);

        return $paymentMethods->data;
    }

    public function retrievePaymentMethod(
        string $paymentMethodId
    ): PaymentMethod {
        return PaymentMethod::retrieve(
            $paymentMethodId
        );
    }

    public function detachPaymentMethod(
        string $paymentMethodId
    ): PaymentMethod {
        $paymentMethod = PaymentMethod::retrieve(
            $paymentMethodId
        );

        return $paymentMethod->detach();
    }

    public function createPaymentIntent(
        array $parameters,
        ?string $idempotencyKey = null
    ): PaymentIntent {
        return PaymentIntent::create(
            $parameters,
            $this->requestOptions($idempotencyKey)
        );
    }

    private function requestOptions(
        ?string $idempotencyKey
    ): array {
        if (!$idempotencyKey) {
            return [];
        }

        return [
            'idempotency_key' => $idempotencyKey,
        ];
    }
}