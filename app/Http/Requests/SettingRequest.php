<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('setting.update') ?? false;
    }

    public function rules(): array
    {
        return ['settings' => ['required', 'array', 'max:100'], 'settings.*.key' => ['required', 'string', 'max:255'],
            'settings.*.group' => ['sometimes', 'string', 'max:100'], 'settings.*.value' => ['nullable'],
            'settings.*.type' => ['sometimes', Rule::in(['string', 'boolean', 'integer', 'json'])],
            'settings.*.is_public' => ['sometimes', 'boolean']];
    }
}
