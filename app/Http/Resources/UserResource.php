<?php

namespace App\Http\Resources;

use App\Modules\SecuritySetting\Services\SecuritySettingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role_id' => $this->roles->first()?->id,
            'role_ids' => $this->roles->pluck('id')->values(),
            'avatar_path' => $this->avatar_path,
            'avatar_url' => $this->avatar_url,
            'is_active' => $this->is_active,
            'status' => $this->status,
            'failed_login_attempts' => $this->failed_login_attempts,
            'last_failed_login_at' => $this->last_failed_login_at?->toISOString(),
            'locked_at' => $this->locked_at?->toISOString(),
            'is_locked' => $this->locked_at !== null,
            'two_factor_global_enabled' => app(SecuritySettingService::class)->isAdminTwoFactorEnabled(),
            'roles' => $this->roles->pluck('name')->values(),
            'permissions' => $this->getAllPermissions()->pluck('name')->values(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
