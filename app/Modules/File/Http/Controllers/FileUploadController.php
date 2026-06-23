<?php

namespace App\Modules\File\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\File\UploadDocumentRequest;
use App\Http\Requests\File\UploadImageRequest;
use App\Http\Resources\FileUploadResource;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;

class FileUploadController extends BaseController
{
    public function __construct(private readonly FileUploadService $uploads) {}

    public function image(UploadImageRequest $request): JsonResponse
    {
        $directory = $request->validated('directory') ?? 'images';

        return $this->success('Image uploaded.', new FileUploadResource(
            $this->uploads->store($request->file('image'), $directory)
        ), 201);
    }

    public function document(UploadDocumentRequest $request): JsonResponse
    {
        $directory = $request->validated('directory') ?? 'documents';

        return $this->success('Document uploaded.', new FileUploadResource(
            $this->uploads->store($request->file('document'), $directory)
        ), 201);
    }
}
