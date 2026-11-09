<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Every API request is handled as JSON (no HTML error pages).
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Every API error follows { success, message, data, errors }.
        // App\Support\Exceptions\DomainException renders itself (see its render()).
        $respond = static fn (string $message, int $status, mixed $errors = null): JsonResponse => response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $status);

        $exceptions->render(fn (ValidationException $e, Request $request) => $request->is('api/*')
            ? $respond('Validation failed', 422, $e->errors())
            : null);

        $exceptions->render(fn (AuthenticationException $e, Request $request) => $request->is('api/*')
            ? $respond('Unauthenticated', 401)
            : null);

        $exceptions->render(fn (NotFoundHttpException $e, Request $request) => $request->is('api/*')
            ? $respond('The requested resource was not found', 404)
            : null);

        $exceptions->render(fn (MethodNotAllowedHttpException $e, Request $request) => $request->is('api/*')
            ? $respond('Method not allowed', 405)
            : null);

        $exceptions->render(fn (HttpException $e, Request $request) => $request->is('api/*')
            ? $respond($e->getMessage() ?: 'An error occurred', $e->getStatusCode())
            : null);

        // Never leak internals in production.
        $exceptions->render(fn (Throwable $e, Request $request) => $request->is('api/*')
            ? $respond(config('app.debug') ? $e->getMessage() : 'Internal server error', 500)
            : null);
    })->create();
