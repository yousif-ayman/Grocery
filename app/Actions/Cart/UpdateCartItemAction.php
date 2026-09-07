<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;

class UpdateCartItemAction
{
    public function execute(
        Cart $cart,
        CartItem $cartItem,
        int $quantity
    ): void {
        if ($cartItem->meal->stock_quantity < $quantity) {
            throw new \Exception(
                "Only {$cartItem->meal->stock_quantity} items available in stock"
            );
        }

        DB::transaction(function () use (
            $cart,
            $cartItem,
            $quantity
        ) {
            $cartItem->update([
                'quantity' => $quantity,
            ]);

            $cart->calculateTotals();
        });
    }
}