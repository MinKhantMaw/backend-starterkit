<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('page.'.($this->isMethod('post') ? 'create' : 'update')) ?? false;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'title' => [$required, 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'], 'status' => ['sometimes', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'], 'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'], 'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'], 'og_image' => ['nullable', 'string', 'max:2048'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
