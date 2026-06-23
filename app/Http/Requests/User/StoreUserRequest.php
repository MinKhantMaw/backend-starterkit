<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['required', 'string', 'min:11', 'max:20', Rule::unique('users', 'phone')],
            'password' => ['required', 'string', 'min:6', 'max:15', 'confirmed'],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
