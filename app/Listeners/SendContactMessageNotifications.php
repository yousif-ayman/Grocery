<?php

namespace App\Listeners;

use App\Events\ContactMessageSubmitted;
use App\Jobs\SendContactMessageEmails;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendContactMessageNotifications implements ShouldQueue
{
    public function handle(
        ContactMessageSubmitted $event
    ): void {
        SendContactMessageEmails::dispatch(
            $event->contactMessage->id
        );
    }
}