<?php

namespace App\Actions\Faq;

use App\Models\Faq;

class CreateFaqsAction
{
    public function execute(array $data): Faq
    {
        return Faq::create($data);
    }
}