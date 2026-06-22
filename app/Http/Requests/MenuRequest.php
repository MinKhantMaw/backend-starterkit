<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menu.'.($this->isMethod('post') ? 'create' : 'update')) ?? false;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';
        $menu = $this->route('menu');

        return [
            'name' => [$required, 'string', 'max:255'],
            'location' => [$required, 'string', 'max:100', Rule::unique('menus', 'location')->ignore($menu)],
            'is_active' => ['sometimes', 'boolean'], 'items' => ['sometimes', 'array'],
            'items.*.label' => ['required_with:items', 'string', 'max:255'],
            'items.*.url' => ['required_with:items', 'string', 'max:2048'],
            'items.*.target' => ['sometimes', Rule::in(['_self', '_blank'])],
            'items.*.sort_order' => ['sometimes', 'integer', 'min:0'], 'items.*.is_active' => ['sometimes', 'boolean'],
            'items.*.children' => ['sometimes', 'array'],
            'items.*.children.*.label' => ['required', 'string', 'max:255'],
            'items.*.children.*.url' => ['required', 'string', 'max:2048'],
        ];
    }
}
