<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,csv,txt', 'max:10240'],
            'directory' => ['nullable', 'string', 'max:80', 'regex:/^[a-zA-Z0-9\/_-]+$/'],
        ];
    }
}
