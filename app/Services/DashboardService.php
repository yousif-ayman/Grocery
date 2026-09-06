<?php

namespace App\Services\Dashboard;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get all dashboard data for the authenticated user.
     */
    public function getDashboard(User $user): array
    {
        return [
            'overview' => $this->getOverview($user),
            'shopping_insights' => $this->getShoppingInsights($user),
            'category_distribution' => $this->getCategoryDistribution($user),
            'recent_orders' => $this->getRecentOrders($user),
            'top_purchases' => $this->getTopPurchases($user),
        ];
    }

    /**
     * Get dashboard overview.
     */
    private function getOverview(User $user): array
    {
        /*
        |--------------------------------------------------------------------------
        | Active Order
        |--------------------------------------------------------------------------
        */

        $activeOrder = Order::query()
            ->where('user_id', $user->id)
            ->whereNotIn('status', [
                'cancelled',
                'delivered',
            ])
            ->orderByDesc('created_at')
            ->first();

        $trackingOrder = $activeOrder
            ? [
                'id' => $activeOrder->id,
                'order_number' => $activeOrder->order_number,
                'status' => $activeOrder->status,
                'status_description' => $activeOrder->status_description,
                'status_position' => $activeOrder->status_position,
            ]
            : null;

        /*
        |--------------------------------------------------------------------------
        | Current Cart
        |--------------------------------------------------------------------------
        */

        $cart = $user->activeCart()
            ->with('items')
            ->first();

        $cartData = [
            'items_count' => 0,
            'total' => 0,
            'last_updated' => null,
        ];

        if ($cart) {
            $cart->calculateTotals();

            $cartData = [
                'items_count' => $cart->items->sum('quantity'),
                'total' => (float) $cart->total,
                'last_updated' => $cart->updated_at,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Upcoming Delivery
        |--------------------------------------------------------------------------
        */

        $upcomingDelivery = Order::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [
                'placed',
                'processing',
                'shipping',
                'out_for_delivery',
            ])
            ->whereNotNull('estimated_delivery_time')
            ->orderBy('estimated_delivery_time')
            ->first();

        $upcomingDeliveryData = $upcomingDelivery
            ? [
                'order_id' => $upcomingDelivery->id,
                'order_number' => $upcomingDelivery->order_number,
                'date' => $upcomingDelivery
                    ->estimated_delivery_time
                    ?->format('Y-m-d'),
                'time' => $upcomingDelivery
                    ->estimated_delivery_time
                    ?->format('H:i'),
                'estimated_delivery_time' => $upcomingDelivery
                    ->estimated_delivery_time,
            ]
            : null;

        return [
            'tracking_order' => $trackingOrder,
            'loyalty_points' => (int) ($user->loyalty_points ?? 0),
            'store_credits' => (float) ($user->store_credits ?? 0),
            'current_cart' => $cartData,
            'upcoming_delivery' => $upcomingDeliveryData,
        ];
    }

    /**
     * Get shopping insights.
     */
    private function getShoppingInsights(User $user): array
    {
        /*
        |--------------------------------------------------------------------------
        | Current Month
        |--------------------------------------------------------------------------
        */

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | Orders This Month
        |--------------------------------------------------------------------------
        */

        $ordersThisMonth = Order::query()
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [
                $startOfMonth,
                $endOfMonth,
            ])
            ->where('status', '!=', 'cancelled')
            ->get([
                'created_at',
                'total',
            ]);

        $ordersCount = $ordersThisMonth->count();

        /*
        |--------------------------------------------------------------------------
        | Monthly Spend
        |--------------------------------------------------------------------------
        */

        $monthlySpend = (float) $ordersThisMonth->sum('total');

        /*
        |--------------------------------------------------------------------------
        | Average Days Between Orders
        |--------------------------------------------------------------------------
        */

        $averageDaysBetweenOrders = 0;

        if ($ordersCount > 1) {
            $orderDates = $ordersThisMonth
                ->pluck('created_at')
                ->sort()
                ->values();

            $totalDays = 0;
            $intervals = 0;

            for ($i = 1; $i < $orderDates->count(); $i++) {
                $totalDays += $orderDates[$i]->diffInDays(
                    $orderDates[$i - 1]
                );

                $intervals++;
            }

            if ($intervals > 0) {
                $averageDaysBetweenOrders = round(
                    $totalDays / $intervals,
                    1
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Order Discounts
        |--------------------------------------------------------------------------
        */

        $orderDiscounts = (float) Order::query()
            ->where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->sum('discount');

        /*
        |--------------------------------------------------------------------------
        | Meal Discounts
        |--------------------------------------------------------------------------
        */

        $mealSavings = OrderItem::query()
            ->whereHas('order', function ($query) use ($user) {
                $query
                    ->where('user_id', $user->id)
                    ->where('status', '!=', 'cancelled');
            })
            ->with('meal')
            ->get()
            ->sum(function ($item) {
                if (
                    $item->meal &&
                    $item->meal->discount_price
                ) {
                    return (
                        $item->meal->price
                        - $item->meal->discount_price
                    ) * $item->quantity;
                }

                return 0;
            });

        /*
        |--------------------------------------------------------------------------
        | Total Savings
        |--------------------------------------------------------------------------
        */

        $totalSavings = $orderDiscounts + $mealSavings;

        /*
        |--------------------------------------------------------------------------
        | Average Order Value
        |--------------------------------------------------------------------------
        */

        $averageOrderValue = $ordersCount > 0
            ? round($monthlySpend / $ordersCount, 2)
            : 0;

        return [
            'monthly_spend' => $monthlySpend,

            'orders_this_month' => [
                'count' => $ordersCount,
                'average_days_between_orders' => $averageDaysBetweenOrders,
            ],

            'total_savings' => (float) $totalSavings,

            'average_order_value' => $averageOrderValue,
        ];
    }

    /**
     * Get category distribution.
     */
    private function getCategoryDistribution(User $user): array
    {
        $orderItems = OrderItem::query()
            ->whereHas('order', function ($query) use ($user) {
                $query
                    ->where('user_id', $user->id)
                    ->where('status', '!=', 'cancelled');
            })
            ->with('meal.category')
            ->get();

        $categoryTotals = [];
        $totalItems = 0;

        foreach ($orderItems as $item) {
            $meal = $item->meal;

            if (! $meal || ! $meal->category) {
                continue;
            }

            $category = $meal->category;
            $categoryId = $category->id;
            $quantity = (int) $item->quantity;

            if (! isset($categoryTotals[$categoryId])) {
                $categoryTotals[$categoryId] = [
                    'category_id' => $categoryId,
                    'category_name' => $category->name,
                    'total_quantity' => 0,
                ];
            }

            $categoryTotals[$categoryId]['total_quantity'] += $quantity;
            $totalItems += $quantity;
        }

        $distribution = [];

        foreach ($categoryTotals as $data) {
            $percentage = $totalItems > 0
                ? round(
                    ($data['total_quantity'] / $totalItems) * 100,
                    1
                )
                : 0;

            $distribution[] = [
                'category_id' => $data['category_id'],
                'category_name' => $data['category_name'],
                'total_quantity' => $data['total_quantity'],
                'percentage' => $percentage,
            ];
        }

        usort(
            $distribution,
            fn (array $a, array $b) =>
                $b['percentage'] <=> $a['percentage']
        );

        return $distribution;
    }

    /**
     * Get recent orders.
     */
    private function getRecentOrders(
        User $user,
        int $limit = 5
    ): array {
        $orders = Order::query()
            ->where('user_id', $user->id)
            ->with('items')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $orders
            ->map(function (Order $order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'status_description' => $order->status_description,
                    'total' => (float) $order->total,
                    'created_at' => $order->created_at,
                    'items_count' => $order->items->sum('quantity'),
                ];
            })
            ->toArray();
    }

    /**
     * Get top purchased meals.
     */
    private function getTopPurchases(
        User $user,
        int $limit = 10
    ): array {
        $topMeals = OrderItem::query()
            ->whereHas('order', function ($query) use ($user) {
                $query
                    ->where('user_id', $user->id)
                    ->where('status', '!=', 'cancelled');
            })
            ->with('meal.category')
            ->select(
                'meal_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(subtotal) as total_spent')
            )
            ->groupBy('meal_id')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();

        return $topMeals
            ->map(function (OrderItem $item) {
                $meal = $item->meal;

                return [
                    'meal_id' => $meal?->id,
                    'title' => $meal?->title,
                    'image_url' => $meal?->image_url,

                    'category' => $meal?->category
                        ? [
                            'id' => $meal->category->id,
                            'name' => $meal->category->name,
                        ]
                        : null,

                    'total_quantity_purchased' =>
                        (int) $item->total_quantity,

                    'total_spent' =>
                        (float) $item->total_spent,
                ];
            })
            ->toArray();
    }
}