<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateImageRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\AddressResource;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProfileResource;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $this->profileService->getProfileData($user);

        return $this->successResponse([
            'me' => new ProfileResource($user),
            'addresses' => AddressResource::collection($data['addresses']),
            'order_history' => [
                'orders' => OrderResource::collection($data['orderHistory']),
                'ordered_at' => $data['orderHistory']->map(fn ($o) => $o['placed_at'] ?? $o['created_at'])->values(),
            ],
            'in_progress_orders' => OrderDetailResource::collection($data['inProgressOrders']),
            'order_notifications' => $data['orderNotifications'],
            'settings' => $data['settings'],
            'wishlist' => $data['wishlist'],
        ], 'Profile retrieved successfully');
    }

    public function orderHistory(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->get('per_page', 15), 1), 50);
        $orders = $this->profileService->getOrderHistory($request->user(), $perPage);

        return $this->successResponse(OrderResource::collection($orders), 'Order history retrieved successfully');
    }

    public function updateImage(UpdateImageRequest $request): JsonResponse
    {
        $user = $this->profileService->updateProfileImage($request->user(), $request->file('image'));

        return $this->successResponse([
            'profile_image' => $user->profile_image,
            'profile_image_url' => $user->profile_image_url,
        ], 'Profile image updated successfully');
    }

    public function updateInfo(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->profileService->updateProfileInfo($request->user(), $request->validated());

        if (!$user) {
            return $this->errorResponse('No data provided to update');
        }

        return $this->successResponse(new ProfileResource($user), 'Profile updated successfully');
    }

    public function deleteImage(Request $request): JsonResponse
    {
        $this->profileService->deleteProfileImage($request->user());
        return $this->successResponse(null, 'Profile image deleted successfully');
    }

    public function sessions(Request $request): JsonResponse
    {
        $currentTokenId = $request->user()->currentAccessToken()?->id;
        $tokens = $request->user()->tokens()->get()->map(fn ($token) => [
            'id' => $token->id, 'name' => $token->name,
            'last_used_at' => $token->last_used_at?->toIso8601String(),
            'is_current' => (string) $token->id === (string) $currentTokenId,
            'created_at' => $token->created_at?->toIso8601String(),
        ]);

        return $this->successResponse($tokens, 'Sessions retrieved successfully');
    }

    public function destroySession(Request $request, string $tokenId): JsonResponse
    {
        $user = $request->user();
        $currentTokenId = $user->currentAccessToken()?->id;

        if ((string) $tokenId === (string) $currentTokenId) {
            return $this->errorResponse('Cannot revoke your current session. Use logout instead.');
        }

        $token = $user->tokens()->find($tokenId);
        if (!$token) {
            return $this->errorResponse('Session not found', 404);
        }

        $token->delete();
        return $this->successResponse(null, 'Session revoked successfully');
    }
}
