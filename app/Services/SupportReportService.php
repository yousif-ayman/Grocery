<?php

namespace App\Services;

use App\Models\SupportReport;
use App\Models\User;

class SupportReportService
{
    public function create(
        User $user,
        array $data,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): SupportReport {
        return SupportReport::create([
            'user_id' => $user->id,
            'issue_type' => $data['issue_type'],
            'order_number' => $data['order_number'] ?? null,
            'message' => $data['message'],
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }
}