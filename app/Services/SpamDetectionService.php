<?php

namespace App\Services\Contact;

class SpamDetectionService
{
    private const SPAM_KEYWORDS = [
        'viagra',
        'casino',
        'loan',
        'debt',
        'free money',
        'work from home',
        'make money fast',
        'click here',
    ];

    public function isSpam(string $message,): bool
    {
        $content = strtolower($message);

        foreach (self::SPAM_KEYWORDS as $keyword) {
            if (str_contains($content, $keyword)) {
                return true;
            }
        }

        return false;
    }
}