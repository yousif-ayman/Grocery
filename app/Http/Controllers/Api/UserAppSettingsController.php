<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAppearanceRequest;
use App\Http\Requests\UpdateLanguageRequest;
use App\Http\Requests\UpdateNotificationPreferencesRequest;
use App\Services\UserAppSettingsService;
use Illuminate\Http\JsonResponse;

class UserAppSettingsController extends Controller
{
    public function __construct(
        private readonly UserAppSettingsService $settingsService,
    ) {}

    public function showLanguage(): JsonResponse
    {
        return $this->successResponse(
            data: $this->settingsService->getLanguage(auth()->user()),
            message: 'Language retrieved successfully.',
        );
    }

    public function updateLanguage(
        UpdateLanguageRequest $request
    ): JsonResponse {
        $data = $this->settingsService->updateLanguage(
            $request->user(),
            $request->validated('language'),
        );

        return $this->successResponse(
            data: $data,
            message: 'Language updated successfully.',
        );
    }

    public function showAppearance(): JsonResponse
    {
        return $this->successResponse(
            data: $this->settingsService->getAppearance(
                request()->user(),
            ),
            message: 'Appearance retrieved successfully.',
        );
    }

    public function updateAppearance(
        UpdateAppearanceRequest $request
    ): JsonResponse {
        $data = $this->settingsService->updateAppearance(
            $request->user(),
            $request->validated('theme'),
        );

        return $this->successResponse(
            data: $data,
            message: 'Appearance updated successfully.',
        );
    }

    public function showNotificationPreferences(): JsonResponse
    {
        return $this->successResponse(
            data: $this->settingsService->getNotificationPreferences(
                request()->user(),
            ),
            message: 'Notification preferences retrieved successfully.',
        );
    }

    public function updateNotificationPreferences(
        UpdateNotificationPreferencesRequest $request
    ): JsonResponse {
        $data = $this->settingsService->updateNotificationPreferences(
            $request->user(),
            $request->validated(),
        );

        return $this->successResponse(
            data: $data,
            message: 'Notification preferences updated successfully.',
        );
    }
}