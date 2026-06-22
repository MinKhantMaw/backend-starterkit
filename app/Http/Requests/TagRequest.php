<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tag.'.($this->isMethod('post') ? 'create' : 'update')) ?? false;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';

        return ['name' => [$required, 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255']];
    }
}
