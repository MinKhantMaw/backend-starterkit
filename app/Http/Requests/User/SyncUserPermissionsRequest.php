<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncUserPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('user.update');
    }

    public function rules(): array
    {
        return ['permissions' => ['required', 'array'], 'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')]];
    }
}
