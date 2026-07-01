<?php

namespace App\Modules\SecuritySetting\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecuritySettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'max_login_attempts' => (int) $this->resource['max_login_attempts'],
            'lock_account_enabled' => (bool) $this->resource['lock_account_enabled'],
            'login_rate_limit_enabled' => (bool) $this->resource['login_rate_limit_enabled'],
            'remember_me_enabled' => (bool) $this->resource['remember_me_enabled'],
            'password_history_count' => (int) $this->resource['password_history_count'],
            'password_expiry_days' => (int) $this->resource['password_expiry_days'],
            'force_password_change_enabled' => (bool) $this->resource['force_password_change_enabled'],
            'admin_2fa_enabled' => (bool) $this->resource['admin_2fa_enabled'],
        ];
    }
}
