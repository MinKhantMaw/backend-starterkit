<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'success' => $this->success,
            'failure_reason' => $this->failure_reason,
            'logged_in_at' => $this->logged_in_at?->toISOString(),
            'logged_out_at' => $this->logged_out_at?->toISOString(),
        ];
    }
}
