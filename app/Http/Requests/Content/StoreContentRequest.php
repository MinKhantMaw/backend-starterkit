<?php

namespace App\Http\Requests\Content;

use App\Models\Content;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('content.create');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:contents,slug'],
            'excerpt' => ['nullable', 'string'],
            'body' => ['required', 'string'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'status' => ['sometimes', Rule::in([Content::STATUS_DRAFT, Content::STATUS_PUBLISHED, Content::STATUS_ARCHIVED])],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
