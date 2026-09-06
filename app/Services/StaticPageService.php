<?php

namespace App\Services;

use App\Models\StaticPage;
use Illuminate\Http\Request;

class StaticPageService
{
    public function getPages(Request $request): object
    {
        $query = StaticPage::query();

        if (!$request->user() || !$request->user()->is_admin) {
            $query->published();
        }

        if ($request->has('published')) $query->where('is_published', $request->boolean('published'));

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('title', 'LIKE', "%{$search}%")->orWhere('content', 'LIKE', "%{$search}%"));
        }

        $perPage = min(max((int) $request->get('per_page', 15), 1), 50);
        return $query->ordered()->paginate($perPage);
    }

    public function createPage(array $data): StaticPage
    {
        return StaticPage::create($data);
    }

    public function getPageBySlug(string $slug): ?StaticPage
    {
        return StaticPage::bySlug($slug)->first();
    }

    public function isPageAccessible(?StaticPage $page, $user): bool
    {
        if (!$page) return false;
        if ($page->is_published) return true;
        return $user && $user->is_admin;
    }

    public function getPageById(int $id): StaticPage
    {
        return StaticPage::findOrFail($id);
    }

    public function updatePage(StaticPage $page, array $data): StaticPage
    {
        $page->update($data);
        return $page;
    }

    public function deletePage(StaticPage $page): bool
    {
        return $page->delete();
    }

    public function getImportantPages(): \Illuminate\Database\Eloquent\Collection
    {
        return StaticPage::published()
            ->whereIn('slug', ['terms-and-conditions', 'policies', 'about-us', 'contact-us'])
            ->ordered()->get(['slug', 'title']);
    }
}
