<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('permissions.edit');
    }

    public function rules(): array
    {
        $permission = $this->route('permission');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->where('guard_name', 'web')->ignore($permission->id)],
        ];
    }
}
