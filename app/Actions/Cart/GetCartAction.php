<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Services\ShippingService;

class GetCartAction
{
    public function execute(
        Cart $cart,
        ?string $deliveryType = null
    ): array {
        $cart->load([
            'items.meal.category',
            'items.meal.subcategory',
        ]);

        if (
            $deliveryType &&
            in_array($deliveryType, ['delivery', 'pickup'], true)
        ) {
            $shippingService = app(ShippingService::class);

            $shippingFee = $shippingService->calculateShippingFee(
                (float) $cart->subtotal,
                $deliveryType
            );

            $totalWithShipping =
                (float) $cart->total + $shippingFee;

            return [
                'shipping_fee' => $shippingFee,
                'total_with_shipping' => $totalWithShipping,
            ];
        }

        return [
            'shipping_fee' => null,
            'total_with_shipping' => null,
        ];
    }
}