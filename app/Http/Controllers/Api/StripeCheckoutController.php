<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStripeCheckoutSessionRequest;
use App\Models\Order;
use App\Services\StripeCheckoutService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\ApiErrorException;
use Throwable;

class StripeCheckoutController extends Controller
{
    public function __construct(
        private readonly StripeCheckoutService $checkoutService
    ) {}

    public function store(
        CreateStripeCheckoutSessionRequest $request
    ): JsonResponse {
        $user = $request->user();

        $order = Order::query()
            ->find($request->validated('order_id'));

        if (! $order) {
            return $this->errorResponse(
                'Order not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        $this->authorize('pay', $order);

        try {
            $session = $this->checkoutService
                ->createSessionForOrder(
                    $order,
                    $user
                );

            return $this->successResponse(
                [
                    'checkout_url' => $session->url,
                    'session_id' => $session->id,
                    'order_id' => $order->id,
                ],
                'Checkout session created.'
            );
        } catch (DomainException $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (ApiErrorException $e) {
            report($e);

            return $this->errorResponse(
                'Unable to start payment. Please try again.',
                Response::HTTP_BAD_GATEWAY
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse(
                'Unable to start checkout. Please try again.',
                Response::HTTP_BAD_GATEWAY
            );
        }
    }

    public function verifySession(
        Request $request,
        string $sessionId
    ): JsonResponse {
        $user = $request->user();

        $order = Order::query()
            ->where('user_id', $user->id)
            ->where('stripe_checkout_session_id', $sessionId)
            ->first();

        if (! $order) {
            return $this->errorResponse(
                'Payment session not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $order = $this->checkoutService
                ->verifySession(
                    $sessionId,
                    $order
                );

            return $this->successResponse(
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status->value,
                ],
                'Payment verified. Order is placed.'
            );
        } catch (DomainException $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_PAYMENT_REQUIRED
            );
        } catch (ApiErrorException $e) {
            report($e);

            return $this->errorResponse(
                'Unable to verify payment session.',
                Response::HTTP_BAD_GATEWAY
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse(
                'Unable to verify payment session.',
                Response::HTTP_BAD_GATEWAY
            );
        }
    }
}