<?php

namespace App\Http\Controllers\Api;

use App\Actions\Contact\GetContactMessagesAction;
use App\Actions\Contact\SubmitContactMessageAction;
use App\Actions\Contact\UpdateContactMessageStatusAction;
use App\Events\ContactMessageSubmitted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\ContactMessageIndexRequest;
use App\Http\Requests\Contact\SubmitContactMessageRequest;
use App\Http\Requests\Contact\UpdateContactMessageStatusRequest;
use App\Http\Resources\ContactMessageCollection;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use App\Services\Contact\ContactMessageStatisticsService;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function __construct(
        private readonly SubmitContactMessageAction $submitAction,
        private readonly GetContactMessagesAction $getMessagesAction,
        private readonly UpdateContactMessageStatusAction $updateStatusAction,
        private readonly ContactMessageStatisticsService $statisticsService,
    ) {
    }

    public function submit(
        SubmitContactMessageRequest $request
    ): JsonResponse {
        $contactMessage = $this->submitAction->execute(
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        ContactMessageSubmitted::dispatch($contactMessage);

        return response()->json([
            'success' => true,
            'message' => 'Your message has been sent successfully.',
            'data' => new ContactMessageResource($contactMessage),
        ], 201);
    }

    public function index(
        ContactMessageIndexRequest $request
    ): ContactMessageCollection {
        $messages = $this->getMessagesAction->execute(
            $request->validated()
        );

        return new ContactMessageCollection($messages);
    }

    public function show(
        ContactMessage $contactMessage
    ): ContactMessageResource {
        $this->authorize('view', $contactMessage);

        $contactMessage->markAsRead();

        return new ContactMessageResource(
            $contactMessage->refresh()
        );
    }

    public function updateStatus(
        UpdateContactMessageStatusRequest $request,
        ContactMessage $contactMessage
    ): JsonResponse {
        $this->authorize('update', $contactMessage);

        $contactMessage = $this->updateStatusAction->execute(
            $contactMessage,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Contact message updated successfully.',
            'data' => new ContactMessageResource($contactMessage),
        ]);
    }

    public function destroy(
        ContactMessage $contactMessage
    ): JsonResponse {
        $this->authorize('delete', $contactMessage);

        $contactMessage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact message deleted successfully.',
        ]);
    }

    public function statistics(): JsonResponse
    {
        $this->authorize(
            'viewAny',
            ContactMessage::class
        );

        return response()->json([
            'success' => true,
            'data' => $this->statisticsService->getStatistics(),
        ]);
    }
}