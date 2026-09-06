<?php

namespace App\Actions\Faq;

use App\Models\Faq;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListFaqsAction
{
    public function execute(array $filters): LengthAwarePaginator
    {
        $query = Faq::query();

        if (! empty($filters['category'])) {
            $query->category($filters['category']);
        }

        if ($filters['active_only'] ?? true) {
            $query->active();
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($query) use ($search) {
                $query->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        return $query
            ->ordered()
            ->paginate($filters['per_page'] ?? 15);
    }


}