<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChargeSavedCardRequest;
use App\Services\Payment\SavedCardPaymentService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\ApiErrorException;
use Throwable;

class StripeController extends Controller
{
    public function __construct(
        private readonly SavedCardPaymentService $paymentService
    ) {}

    public function createSetupIntent(
        Request $request
    ): JsonResponse {
        try {
            $data = $this->paymentService
                ->createSetupIntent(
                    $request->user()
                );

            return $this->successResponse(
                $data,
                'Setup intent created.'
            );
        } catch (ApiErrorException $e) {
            report($e);

            return $this->errorResponse(
                'Unable to initialize card setup.',
                Response::HTTP_BAD_GATEWAY
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse(
                'Unable to initialize card setup.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function listCards(
        Request $request
    ): JsonResponse {
        try {
            $cards = $this->paymentService
                ->listCards(
                    $request->user()
                );

            return $this->successResponse(
                $cards,
                'Cards retrieved successfully.'
            );
        } catch (ApiErrorException $e) {
            report($e);

            return $this->errorResponse(
                'Unable to retrieve cards.',
                Response::HTTP_BAD_GATEWAY
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse(
                'Unable to retrieve cards.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function chargeSavedCard(
        ChargeSavedCardRequest $request
    ): JsonResponse {
        $validated = $request->validated();

        $order = $request
            ->user()
            ->orders()
            ->find($validated['order_id']);

        if (!$order) {
            return $this->errorResponse(
                'Order not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            $payment = $this->paymentService
                ->chargeSavedCard(
                    $request->user(),
                    $order,
                    $validated['payment_method_id']
                );

            return $this->successResponse(
                $payment,
                'Payment processed successfully.'
            );
        } catch (DomainException $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (ApiErrorException $e) {
            report($e);

            return $this->errorResponse(
                'Payment could not be processed.',
                Response::HTTP_BAD_GATEWAY
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse(
                'Payment could not be processed.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function deleteCard(
        Request $request,
        string $id
    ): JsonResponse {
        try {
            $this->paymentService
                ->deleteCard(
                    $request->user(),
                    $id
                );

            return $this->successResponse(
                null,
                'Card deleted successfully.'
            );
        } catch (DomainException $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_FORBIDDEN
            );
        } catch (ApiErrorException $e) {
            report($e);

            return $this->errorResponse(
                'Unable to delete card.',
                Response::HTTP_BAD_GATEWAY
            );
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse(
                'Unable to delete card.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}