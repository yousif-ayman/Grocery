<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupportReportRequest;
use App\Http\Resources\Api\SupportReportResource;
use App\Services\SupportReportService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SupportController extends Controller
{
    public function __construct(
        private readonly SupportReportService $supportReportService
    ) {
    }

    /**
     * Submit a support report for the authenticated user.
     */
    public function store(StoreSupportReportRequest $request): JsonResponse
    {
        $report = $this->supportReportService->create(
            user: $request->user(),
            data: $request->validated(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return $this->successResponse(
            data: new SupportReportResource($report),
            message: 'Support report submitted successfully.',
            code: Response::HTTP_CREATED,
        );
    }
}