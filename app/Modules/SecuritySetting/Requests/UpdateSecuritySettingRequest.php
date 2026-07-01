<?php

namespace App\Modules\SecuritySetting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSecuritySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'admin_2fa_enabled' => ['required', 'boolean'],
        ];
    }
}
