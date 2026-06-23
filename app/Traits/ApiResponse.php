<?php

namespace App\Traits;

use App\Helpers\ApiResponse as ApiResponseHelper;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(string $message = 'Success', mixed $data = null, int $status = 200): JsonResponse
    {
        return ApiResponseHelper::success($message, $data, $status);
    }

    protected function errorResponse(string $message = 'Error', mixed $errors = null, int $status = 400): JsonResponse
    {
        return ApiResponseHelper::error($message, $errors, $status);
    }

    protected function validationErrorResponse(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, $errors, 422);
    }

    protected function notFoundResponse(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->errorResponse($message, status: 404);
    }

    protected function unauthorizedResponse(string $message = 'Unauthenticated.'): JsonResponse
    {
        return $this->errorResponse($message, status: 401);
    }

    protected function forbiddenResponse(string $message = 'Forbidden.'): JsonResponse
    {
        return $this->errorResponse($message, status: 403);
    }
}
