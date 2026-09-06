<?php

namespace App\Services\Contact;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\DB;

class ContactMessageStatisticsService
{
    public function getStatistics(): array
    {
        $statusCounts = ContactMessage::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $monthly = ContactMessage::query()
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'total' => ContactMessage::count(),

            'new' => (int) ($statusCounts['new'] ?? 0),

            'read' => (int) ($statusCounts['read'] ?? 0),

            'replied' => (int) ($statusCounts['replied'] ?? 0),

            'spam' => (int) ($statusCounts['spam'] ?? 0),

            'monthly' => $monthly,
        ];
    }
}