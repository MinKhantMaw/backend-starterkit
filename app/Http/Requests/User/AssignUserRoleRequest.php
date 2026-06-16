<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('user.update');
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ];
    }
}
