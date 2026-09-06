<?php

namespace App\Http\Controllers\Api;

use App\Actions\Faq\CreateFaqAction;
use App\Actions\Faq\CreateFaqsAction;
use App\Actions\Faq\DeleteFaqAction;
use App\Actions\Faq\DeleteFaqsAction;
use App\Actions\Faq\GetFaqCategoriesAction;
use App\Actions\Faq\GetFaqsByCategoryAction;
use App\Actions\Faq\ListFaqsAction;
use App\Actions\Faq\UpdateFaqAction;
use App\Actions\Faq\UpdateFaqsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Faq\FaqIndexRequest;
use App\Http\Requests\Faq\StoreFaqRequest;
use App\Http\Requests\Faq\UpdateFaqRequest;
use App\Http\Resources\FaqCollection;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use App\Support\ApiResponse;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class FaqController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        private readonly ListFaqsAction $listFaqsAction,
        private readonly CreateFaqsAction $createFaqAction,
        private readonly UpdateFaqsAction $updateFaqAction,
        private readonly DeleteFaqsAction $deleteFaqAction,
        private readonly GetFaqCategoriesAction $getFaqCategoriesAction,
        private readonly GetFaqsByCategoryAction $getFaqsByCategoryAction,
    ) {}

    public function index(FaqIndexRequest $request)
    {
        $faqs = $this->listFaqsAction->execute(
            $request->validated()
        );

        $response = [
            'data' => new FaqCollection($faqs),
        ];

        if ($request->boolean('with_categories')) {
            $response['categories'] = $this->getFaqCategoriesAction->execute();
        }

        return response()->json($response);
    }

    public function store(StoreFaqRequest $request): JsonResponse
    {
        $this->authorize('create', Faq::class);

        $faq = $this->createFaqAction->execute(
            $request->validated()
        );

        return $this->success(
            data: new FaqResource($faq),
            message: 'FAQ created successfully',
            status: 201
        );
    }

    public function show(Faq $faq)
    {
        return new FaqResource($faq);
    }

    public function update(
        UpdateFaqRequest $request,
        Faq $faq
    ): JsonResponse {
        $this->authorize('update', $faq);

        $faq = $this->updateFaqAction->execute(
            $faq,
            $request->validated()
        );

        return $this->success(
            data: new FaqResource($faq),
            message: 'FAQ updated successfully'
        );
    }

    public function destroy(Faq $faq): JsonResponse
    {
        $this->authorize('delete', $faq);

        $this->deleteFaqAction->execute($faq);

        return $this->success(
            message: 'FAQ deleted successfully'
        );
    }

    public function categories(): JsonResponse
    {
        $categories = $this->getFaqCategoriesAction->execute();

        return $this->success(
            data: $categories
        );
    }

    public function byCategory(string $category)
    {
        $faqs = $this->getFaqsByCategoryAction->execute($category);

        return FaqResource::collection($faqs);
    }
}