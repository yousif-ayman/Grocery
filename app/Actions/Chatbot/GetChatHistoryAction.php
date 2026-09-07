<?php

namespace App\Actions\Chatbot;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetChatHistoryAction
{
    public function execute(
        User $user,
        int $perPage = 15
    ): LengthAwarePaginator {
        $perPage = min(max($perPage, 1), 50);

        return $user->chatbotMessages()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}