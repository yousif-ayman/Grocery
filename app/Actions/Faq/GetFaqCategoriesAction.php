<?php

namespace App\Actions\Faq;

use App\Models\Faq;
use Illuminate\Support\Collection;

class GetFaqCategoriesAction
{
    public function execute(): Collection
    {
        return Faq::active()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();
    }
}