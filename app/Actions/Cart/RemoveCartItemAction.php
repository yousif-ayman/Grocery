<?php

namespace App\Actions\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;

class RemoveCartItemAction
{
    public function execute(
        Cart $cart,
        CartItem $cartItem
    ): void {
        DB::transaction(function () use ($cart, $cartItem) {
            $cartItem->delete();

            $cart->calculateTotals();
        });
    }
}