<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('post.'.($this->isMethod('post') ? 'create' : 'update')) ?? false;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'title' => [$required, 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:2000'], 'body' => [$required, 'string'],
            'featured_image' => ['nullable', 'image', 'max:10240'],
            'category_ids' => ['sometimes', 'array'], 'category_ids.*' => ['integer', 'exists:categories,id'],
            'tag_ids' => ['sometimes', 'array'], 'tag_ids.*' => ['integer', 'exists:tags,id'],
            'status' => ['sometimes', Rule::in(['draft', 'published'])], 'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:255'], 'meta_description' => ['nullable', 'string', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:255'], 'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'string', 'max:2048'], 'canonical_url' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
