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
        return response()->json([
            'success' => true,
            'data' => new SettingResource($this->settingService->getSettings())
        ]);
    }

    public function update(SettingRequest $request): JsonResponse
    {
        $settings = $this->settingService->updateSettings(
            $request->validated(),
            $request->file('logo'),
            $request->file('favicon')
        );

        return response()->json([
            'success' => true, 'message' => 'Settings updated successfully',
            'data' => new SettingResource($settings)
        ]);
    }

    public function publicSettings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->settingService->getPublicSettings()
        ]);
    }
}
