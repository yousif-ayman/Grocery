<?php

namespace App\Actions\Faq;

use App\Models\Faq;

class DeleteFaqsAction
{
    public function execute(Faq $faq): void
    {
        $faq->delete();
    }
}