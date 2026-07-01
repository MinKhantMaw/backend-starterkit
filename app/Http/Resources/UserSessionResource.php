<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'device_name' => $this->device_name,
            'browser' => $this->browser,
            'last_activity_at' => $this->last_activity_at?->toISOString(),
            'logged_in_at' => $this->logged_in_at?->toISOString(),
            'logged_out_at' => $this->logged_out_at?->toISOString(),
            'is_current' => $request->user()?->currentAccessToken()?->id === $this->token_id,
        ];
    }
}
