<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactMessageRequest;
use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\CmsNotification;
use App\Services\SettingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class PublicController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    public function settings(): JsonResponse
    {
        return ApiResponse::success('Public settings retrieved.', $this->settings->publicSettings());
    }

    public function contact(ContactMessageRequest $request): JsonResponse
    {
        $message = ContactMessage::query()->create($request->validated());
        User::permission('contact.view')->each(fn (User $user) => $user->notify(new CmsNotification(
            'New contact message',
            "A new contact message was submitted by {$message->name}.",
        )));

        return ApiResponse::success('Message submitted.', null, 201);
    }
}
