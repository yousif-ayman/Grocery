<?php

namespace App\Http\Controllers\Api;

use App\Actions\Cart\AddCartItemAction;
use App\Actions\Cart\ClearCartAction;
use App\Actions\Cart\GetCartAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartItemAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CartResource;
use App\Models\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
class CartController extends Controller
{
    /**
     * Get user's cart
     */
public function index(
    Request $request,
    GetCartAction $action
): JsonResponse {
    try {
        $cart = $request->user()->getOrCreateCart();

        $shipping = $action->execute(
            $cart,
            $request->query('delivery_type')
        );

        $resource = new CartResource($cart);

      $resource->shippingFee = $shipping['shipping_fee'];
$resource->totalWithShipping = $shipping['total_with_shipping'];
        return response()->json([
            'success' => true,
            'message' => 'Cart retrieved successfully',
            'data' => $resource,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve cart',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    /**
     * Add item to cart
     */
    public function addItem(
    Request $request,
    AddCartItemAction $action
): JsonResponse {
    try {
        $maxPerProduct = config(
            'cart.max_quantity_per_product',
            10
        );

        $validated = $request->validate([
            'meal_id' => ['required', 'exists:meals,id'],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:' . $maxPerProduct,
            ],
        ], [
            'quantity.max' =>
                "Maximum {$maxPerProduct} units per product allowed.",
        ]);

        $cart = $request->user()->getOrCreateCart();

        $meal = Meal::findOrFail(
            $validated['meal_id']
        );

        $action->execute(
            $cart,
            $meal,
            $validated['quantity']
        );

        $cart->load([
            'items.meal.category',
            'items.meal.subcategory',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'data' => $this->formatCart($cart),
        ]);
    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 400);
    }
}

    /**
     * Update cart item quantity
     */
   public function updateItem(
    Request $request,
    string $itemId,
    UpdateCartItemAction $action
): JsonResponse {
    try {
        $maxPerProduct = config(
            'cart.max_quantity_per_product',
            10
        );

        $validated = $request->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:' . $maxPerProduct,
            ],
        ], [
            'quantity.max' =>
                "Maximum {$maxPerProduct} units per product allowed.",
        ]);

        $cart = $request->user()->getOrCreateCart();

        $cartItem = $cart->items()->findOrFail($itemId);

        $action->execute(
            $cart,
            $cartItem,
            $validated['quantity']
        );

        $cart->load([
            'items.meal.category',
            'items.meal.subcategory',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'data' => $this->formatCart($cart),
        ]);
    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Cart item not found',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 400);
    }
}

    /**
     * Remove item from cart
     */
    public function removeItem(
    Request $request,
    string $itemId,
    RemoveCartItemAction $action
): JsonResponse {
    try {
        $cart = $request->user()->getOrCreateCart();

        $cartItem = $cart->items()->findOrFail($itemId);

        $action->execute(
            $cart,
            $cartItem
        );

        $cart->load([
            'items.meal.category',
            'items.meal.subcategory',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully',
            'data' => $this->formatCart($cart),
        ]);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Cart item not found',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 400);
    }
}
    /**
     * Clear cart
     */
    public function clear(
    Request $request,
    ClearCartAction $action
): JsonResponse {
    try {
        $cart = $request->user()->getOrCreateCart();

        $action->execute($cart);

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'data' => $this->formatCart($cart),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 400);
    }
}

    /**
     * Format cart data for response.
     * When shipping fee and total_with_shipping are provided (e.g. from delivery_type query), they are included.
     */
  
}
