<?php

namespace App\Services;

use App\Models\User;

class DataExportService
{
    public function loadExportData(User $user): User
    {
        return $user->load([
            'addresses',
            'orders' => fn ($query) => $query
                ->latest()
                ->limit(50),
            'notificationSettings',
        ]);
    }
}