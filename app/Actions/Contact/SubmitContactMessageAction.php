<?php

namespace App\Actions\Contact;

use App\Models\ContactMessage;
use App\Services\Contact\SpamDetectionService;

class SubmitContactMessageAction
{
    public function __construct(
        private readonly SpamDetectionService $spamDetectionService,
    ) {
    }

    public function execute(
        array $data,
        ?string $ipAddress,
        ?string $userAgent
    ): ContactMessage {
        if (
            $this->spamDetectionService->isSpam(
                $data['message']
            )
        ) {
            throw new \DomainException(
                'Your message appears to be spam.'
            );
        }

        return ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }
}