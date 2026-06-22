<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('media.create') ?? false;
    }

    public function rules(): array
    {
        return ['file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg,pdf,doc,docx,xls,xlsx,txt,zip', 'max:25600'],
            'disk' => ['sometimes', Rule::in(['public', 's3'])], 'alt_text' => ['nullable', 'string', 'max:500']];
    }
}
