<?php

namespace App\Actions\Chatbot;

use App\Models\User;
use App\Services\ChatbotService;

class SendChatMessageAction
{
    public function __construct(
        private readonly ChatbotService $chatbotService
    ) {}

    public function execute(
        User $user,
        string $question,
        ?string $conversationId = null,
        ?string $locale = null
    ): array {
        return $this->chatbotService->chat(
            user: $user,
            question: $question,
            conversationId: $conversationId,
            locale: $locale,
        );
    }
}