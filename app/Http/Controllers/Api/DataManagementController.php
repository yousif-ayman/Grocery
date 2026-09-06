<?php

namespace App\Http\Controllers\Api;

use App\Actions\DataManagement\DeleteAccountAction;
use App\Actions\DataManagement\DownloadUserDataAction;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataManagementController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        private readonly DownloadUserDataAction $downloadUserDataAction,
        private readonly DeleteAccountAction $deleteAccountAction,
    ) {}

    public function download(Request $request): StreamedResponse
    {
        $user = $request->user();

        $this->authorize('manageData', $user);

        return $this->downloadUserDataAction->execute($user);
    }

    public function delete(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->authorize('manageData', $user);

        $this->deleteAccountAction->execute($user);

        return $this->success(
            message: 'Account deleted successfully'
        );
    }
}