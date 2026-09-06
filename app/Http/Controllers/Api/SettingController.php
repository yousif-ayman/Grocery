<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Http\Resources\SettingResource;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function __construct(
        private SettingService $settingService
    ) {}

    public function index(): JsonResponse
    {
        return $this->successResponse(
            new SettingResource($this->settingService->getSettings())
        );
    }

    public function update(SettingRequest $request): JsonResponse
    {
        $settings = $this->settingService->updateSettings(
            $request->validated(),
            $request->file('logo'),
            $request->file('favicon')
        );

        return $this->successResponse(
            new SettingResource($settings),
            'Settings updated successfully'
        );
    }

    public function publicSettings(): JsonResponse
    {
        return $this->successResponse($this->settingService->getPublicSettings());
    }
}
