<?php

namespace App\Http\Requests\Content;

use App\Models\Content;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('content.update');
    }

    public function rules(): array
    {
        $content = $this->route('content');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('contents', 'slug')->ignore($content)],
            'excerpt' => ['nullable', 'string'],
            'body' => ['sometimes', 'required', 'string'],
            'featured_image' => ['nullable', 'image', 'max:5120'],
            'status' => ['sometimes', Rule::in([Content::STATUS_DRAFT, Content::STATUS_PUBLISHED, Content::STATUS_ARCHIVED])],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
