<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.edit');
    }

    public function rules(): array
    {
        return [
            'status' => ['required_without:is_active', Rule::in(['active', 'inactive'])],
            'is_active' => ['required_without:status', 'boolean'],
        ];
    }
}
