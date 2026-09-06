<?php

namespace App\Actions\Contact;

use App\Models\ContactMessage;

class UpdateContactMessageStatusAction
{
    public function execute(
        ContactMessage $contactMessage,
        string $status,
        ?string $adminNotes = null
    ): ContactMessage {
        $contactMessage->update([
            'status' => $status,
            'admin_notes' => $adminNotes,
        ]);

        return $contactMessage->refresh();
    }
}