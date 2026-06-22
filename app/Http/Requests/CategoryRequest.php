<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('category.'.($this->isMethod('post') ? 'create' : 'update')) ?? false;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';

        return ['name' => [$required, 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'], 'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort_order' => ['sometimes', 'integer', 'min:0']];
    }
}
