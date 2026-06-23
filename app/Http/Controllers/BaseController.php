<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

abstract class BaseController extends Controller
{
    use ApiResponse;

    protected function success(string $message = 'Success', mixed $data = null, int $status = 200): JsonResponse
    {
        return $this->successResponse($message, $data, $status);
    }

    protected function error(string $message = 'Error', mixed $errors = null, int $status = 400): JsonResponse
    {
        return $this->errorResponse($message, $errors, $status);
    }
}
