<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StaticPageResource;
use App\Http\Resources\StaticPageCollection;
use App\Models\StaticPage;
use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    public function index(Request $request)
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
        return new StaticPageCollection($query->ordered()->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|unique:static_pages,slug|max:100',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'is_published' => 'boolean',
            'order' => 'nullable|integer'
        ]);

        return response()->json([
            'success' => true, 'message' => 'Page created successfully',
            'data' => new StaticPageResource(StaticPage::create($validated))
        ], 201);
    }

    public function showBySlug($slug)
    {
        $page = StaticPage::bySlug($slug)->first();

        if (!$page || (!$page->is_published && (!request()->user() || !request()->user()->is_admin))) {
            return response()->json(['success' => false, 'message' => 'Page not found'], 404);
        }

        return new StaticPageResource($page);
    }

    public function show(StaticPage $staticPage)
    {
        return new StaticPageResource($staticPage);
    }

    public function update(Request $request, StaticPage $staticPage)
    {
        $validated = $request->validate([
            'slug' => 'sometimes|required|string|max:100|unique:static_pages,slug,' . $staticPage->id,
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|array',
            'is_published' => 'sometimes|boolean',
            'order' => 'nullable|integer'
        ]);

        $staticPage->update($validated);

        return response()->json([
            'success' => true, 'message' => 'Page updated successfully',
            'data' => new StaticPageResource($staticPage)
        ]);
    }

    public function destroy(StaticPage $staticPage)
    {
        $staticPage->delete();
        return response()->json(['success' => true, 'message' => 'Page deleted successfully']);
    }

    public function importantPages()
    {
        $pages = StaticPage::published()
            ->whereIn('slug', ['terms-and-conditions', 'policies', 'about-us', 'contact-us'])
            ->ordered()->get(['slug', 'title']);

        return response()->json(['success' => true, 'message' => 'Important pages retrieved successfully', 'data' => $pages]);
    }
}
