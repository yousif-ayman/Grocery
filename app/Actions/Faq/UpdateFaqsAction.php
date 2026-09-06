<?php

namespace App\Actions\Faq;

use App\Models\Faq;

class UpdateFaqsAction
{
    public function execute(Faq $faq, array $data): Faq
    {
        $faq->update($data);

        return $faq->fresh();
    }
}