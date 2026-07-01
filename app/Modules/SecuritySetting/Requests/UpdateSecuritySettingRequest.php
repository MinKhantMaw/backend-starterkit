<?php

namespace App\Modules\SecuritySetting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSecuritySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Super Admin') === true;
    }

    public function rules(): array
    {
        return [
            'max_login_attempts' => ['sometimes', 'integer', 'min:1', 'max:20'],
            'lock_account_enabled' => ['sometimes', 'boolean'],
            'login_rate_limit_enabled' => ['sometimes', 'boolean'],
            'remember_me_enabled' => ['sometimes', 'boolean'],
            'password_history_count' => ['sometimes', 'integer', 'min:1', 'max:24'],
            'password_expiry_days' => ['sometimes', 'integer', 'min:1', 'max:365'],
            'force_password_change_enabled' => ['sometimes', 'boolean'],
            'admin_2fa_enabled' => ['sometimes', 'boolean'],
        ];
    }
}
