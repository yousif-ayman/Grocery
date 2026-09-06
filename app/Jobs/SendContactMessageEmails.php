<?php

namespace App\Jobs;

use App\Mail\ContactAutoReply;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendContactMessageEmails implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $contactMessageId
    ) {
    }

    public function handle(): void
    {
        $contactMessage = ContactMessage::find(
            $this->contactMessageId
        );

        if (! $contactMessage) {
            return;
        }

        Mail::to(
            config('mail.admin_email')
        )->send(
            new ContactMessageReceived($contactMessage)
        );

        Mail::to(
            $contactMessage->email
        )->send(
            new ContactAutoReply($contactMessage)
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error(
            'Failed to send contact message emails.',
            [
                'contact_message_id' => $this->contactMessageId,
                'error' => $exception->getMessage(),
            ]
        );
    }
}