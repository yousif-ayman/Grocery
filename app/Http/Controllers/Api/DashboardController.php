<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\DashboardResource;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $dashboard = $this->dashboardService->getDashboard(
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data retrieved successfully',
            'data' => new DashboardResource($dashboard),
        ]);}}