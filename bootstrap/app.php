<?php

use App\Helpers\ApiResponse;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(HandleCors::class);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'active' => EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            return $request->expectsJson()
                ? ApiResponse::error('Unauthenticated.', status: 401)
                : null;
        });
        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            return $request->expectsJson()
                ? ApiResponse::error('Forbidden.', status: 403)
                : null;
        });
        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request) {
            return $request->expectsJson()
                ? ApiResponse::error('Forbidden.', status: 403)
                : null;
        });
        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            return $request->expectsJson()
                ? ApiResponse::error('Resource not found.', status: 404)
                : null;
        });
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            return $request->expectsJson()
                ? ApiResponse::error('Resource not found.', status: 404)
                : null;
        });
        $exceptions->render(function (MethodNotAllowedHttpException $exception, Request $request) {
            return $request->expectsJson()
                ? ApiResponse::error('Method not allowed.', status: 405)
                : null;
        });
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error('Validation failed', $exception->errors(), 422);
            }

            return null;
        });
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->expectsJson() || $exception instanceof HttpExceptionInterface) {
                return null;
            }

            return ApiResponse::error('Server error.', status: 500);
        });
    })->create();
