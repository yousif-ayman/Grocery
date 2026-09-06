<?php

namespace App\Actions\DataManagement;

use App\Http\Resources\DataExport\UserDataExportResource;
use App\Models\User;
use App\Services\DataExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadUserDataAction
{
    public function __construct(
        private readonly DataExportService $dataExportService,
    ) {}

    public function execute(User $user): StreamedResponse
    {
        $user = $this->dataExportService->loadExportData($user);

        $payload = (new UserDataExportResource($user))
            ->resolve();

        $filename = sprintf(
            'grocery-user-data-%d-%s.json',
            $user->id,
            now()->format('Y-m-d')
        );

        return response()->streamDownload(
            function () use ($payload): void {
                echo json_encode(
                    $payload,
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_UNICODE
                    | JSON_THROW_ON_ERROR
                );
            },
            $filename,
            [
                'Content-Type' => 'application/json',
            ]
        );
    }
}