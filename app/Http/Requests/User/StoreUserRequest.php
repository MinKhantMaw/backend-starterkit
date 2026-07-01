<?php

namespace App\Http\Requests\User;

use App\Enums\PermissionEnum;
use App\Services\PasswordSecurityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionEnum::USER_CREATE->value);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'string', app(PasswordSecurityService::class)->rule(), 'confirmed'],
            'role_id' => ['nullable', 'integer', Rule::exists('roles', 'id')->where('guard_name', 'web'), 'required_without:role_ids'],
            'role_ids' => ['nullable', 'array', 'min:1', 'required_without:role_id'],
            'role_ids.*' => ['integer', Rule::exists('roles', 'id')->where('guard_name', 'web')],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
