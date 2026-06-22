<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SimpleResource;
use App\Models\ContactMessage;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $messages = ContactMessage::query()->when($request->has('read'), fn ($query) => $request->boolean('read') ? $query->whereNotNull('read_at') : $query->whereNull('read_at'))->latest()->paginate(min($request->integer('per_page', 15), 100));

        return ApiResponse::paginated('Contact messages retrieved.', $messages, SimpleResource::class);
    }

    public function show(ContactMessage $contactMessage): JsonResponse
    {
        return ApiResponse::success('Contact message retrieved.', new SimpleResource($contactMessage));
    }

    public function markRead(Request $request, ContactMessage $contactMessage): JsonResponse
    {
        $contactMessage->update(['read_at' => now(), 'read_by' => $request->user()->id]);

        return ApiResponse::success('Message marked as read.', new SimpleResource($contactMessage));
    }

    public function markUnread(ContactMessage $contactMessage): JsonResponse
    {
        $contactMessage->update(['read_at' => null, 'read_by' => null]);

        return ApiResponse::success('Message marked as unread.', new SimpleResource($contactMessage));
    }

    public function destroy(ContactMessage $contactMessage): JsonResponse
    {
        $contactMessage->delete();

        return ApiResponse::success('Contact message deleted.');
    }
}
