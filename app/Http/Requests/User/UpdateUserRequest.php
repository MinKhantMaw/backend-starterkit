<?php

namespace App\Http\Requests\User;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionEnum::USER_UPDATE->value);
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'role_id' => ['nullable', 'integer', Rule::exists('roles', 'id')->where('guard_name', 'web'), 'required_without:role_ids'],
            'role_ids' => ['nullable', 'array', 'min:1', 'required_without:role_id'],
            'role_ids.*' => ['integer', Rule::exists('roles', 'id')->where('guard_name', 'web')],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
