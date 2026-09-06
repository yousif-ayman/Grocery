<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StaticPageCollection;
use App\Http\Resources\StaticPageResource;
use App\Models\StaticPage;
use App\Services\StaticPageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    public function __construct(
        private StaticPageService $staticPageService
    ) {}

    public function index(Request $request)
    {
        return new StaticPageCollection($this->staticPageService->getPages($request));
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

        $page = $this->staticPageService->createPage($validated);

        return $this->successResponse(new StaticPageResource($page), 'Page created successfully', 201);
    }

    public function showBySlug($slug): JsonResponse
    {
        $page = $this->staticPageService->getPageBySlug($slug);

        if (!$this->staticPageService->isPageAccessible($page, request()->user())) {
            return $this->errorResponse('Page not found', 404);
        }

        return $this->successResponse(new StaticPageResource($page));
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

        $staticPage = $this->staticPageService->updatePage($staticPage, $validated);

        return $this->successResponse(new StaticPageResource($staticPage), 'Page updated successfully');
    }

    public function destroy(StaticPage $staticPage): JsonResponse
    {
        $this->staticPageService->deletePage($staticPage);
        return $this->successResponse(null, 'Page deleted successfully');
    }

    public function importantPages(): JsonResponse
    {
        return $this->successResponse($this->staticPageService->getImportantPages(), 'Important pages retrieved successfully');
    }
}
