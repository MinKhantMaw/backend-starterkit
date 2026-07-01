<?php

namespace App\Http\Requests\User;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionEnum::USER_UPDATE->value);
    }

    public function rules(): array
    {
        return [
            'role_id' => ['nullable', 'integer', Rule::exists('roles', 'id')->where('guard_name', 'web'), 'required_without:role_ids'],
            'role_ids' => ['nullable', 'array', 'min:1', 'required_without:role_id'],
            'role_ids.*' => ['integer', Rule::exists('roles', 'id')->where('guard_name', 'web')],
        ];
    }
}
