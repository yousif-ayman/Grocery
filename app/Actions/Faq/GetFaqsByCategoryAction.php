<?php

namespace App\Actions\Faq;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Collection;

class GetFaqsByCategoryAction
{
    public function execute(string $category): Collection
    {
        return Faq::active()
            ->category($category)
            ->ordered()
            ->get();
    }
}