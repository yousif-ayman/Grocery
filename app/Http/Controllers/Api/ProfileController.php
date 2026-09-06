<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\UsernameMustContainLetter;
use App\Services\ProfileService;
use App\Support\EgyptianPhoneRules;
use App\Support\EmailValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data' => $this->profileService->getProfileData($request->user()),
        ]);
    }

    public function orderHistory(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->get('per_page', 15), 1), 50);

        return response()->json([
            'success' => true,
            'message' => 'Order history retrieved successfully',
            'data' => $this->profileService->getOrderHistory($request->user(), $perPage),
        ]);
    }

    public function updateImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = $this->profileService->updateProfileImage($request->user(), $request->file('image'));

        return response()->json([
            'success' => true,
            'message' => 'Profile image updated successfully',
            'data' => [
                'profile_image' => $user->profile_image,
                'profile_image_url' => $user->profile_image_url,
            ],
        ]);
    }

    public function updateInfo(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($request->has('phone') && is_string($request->input('phone'))) {
            $request->merge(['phone' => preg_replace('/\s+/', '', $request->input('phone'))]);
        }

        $validated = $request->validate([
            'username' => ['sometimes', 'string', 'max:'.User::USERNAME_MAX_LENGTH, Rule::unique('users')->ignore($user->id), 'not_regex:/\s/u', 'alpha_dash', new UsernameMustContainLetter],
            'firstname' => ['sometimes', 'string', 'max:255'],
            'lastname' => ['sometimes', 'string', 'max:255'],
            'gender' => ['sometimes', 'nullable', 'string', 'max:20', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'birthday' => ['sometimes', 'nullable', 'date', 'before:today'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['sometimes', 'string', EgyptianPhoneRules::internationalPrefixRule(), 'min:11', 'max:13', EgyptianPhoneRules::mobileRule(), Rule::unique('users')->ignore($user->id)],
            'country_code' => ['sometimes', 'string', 'max:5', 'regex:/^\+\d{1,4}$/'],
            'preferred_languages' => ['sometimes', 'array'],
            'preferred_languages.*' => ['string', 'max:10'],
        ]);

        $user = $this->profileService->updateProfileInfo($user, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id, 'username' => $user->username, 'firstname' => $user->firstname,
                'lastname' => $user->lastname, 'full_name' => $user->full_name, 'gender' => $user->gender,
                'birthday' => $user->birthday?->format('Y-m-d'), 'email' => $user->email, 'phone' => $user->phone,
                'country_code' => $user->country_code, 'preferred_languages' => $user->preferred_languages ?? [],
                'profile_image_url' => $user->profile_image_url, 'updated_at' => $user->updated_at,
            ],
        ]);
    }

    public function deleteImage(Request $request): JsonResponse
    {
        $this->profileService->deleteProfileImage($request->user());
        return response()->json(['success' => true, 'message' => 'Profile image deleted successfully']);
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

        return response()->json(['success' => true, 'message' => 'Sessions retrieved successfully', 'data' => $tokens]);
    }

    public function destroySession(Request $request, string $tokenId): JsonResponse
    {
        $user = $request->user();
        $currentTokenId = $user->currentAccessToken()?->id;

        if ((string) $tokenId === (string) $currentTokenId) {
            return response()->json(['success' => false, 'message' => 'Cannot revoke your current session. Use logout instead.'], 400);
        }

        $token = $user->tokens()->find($tokenId);
        if (! $token) {
            return response()->json(['success' => false, 'message' => 'Session not found'], 404);
        }

        $token->delete();
        return response()->json(['success' => true, 'message' => 'Session revoked successfully']);
    }
}
