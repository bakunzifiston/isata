<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhook/*',
        ]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'system_admin' => \App\Http\Middleware\EnsureUserIsSystemAdmin::class,
            'organization' => \App\Http\Middleware\EnsureUserBelongsToOrganization::class,
        ]);
        $middleware->redirectUsersTo('/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (Throwable $e, $request) {
            // In debug mode, keep Laravel's default detailed error page.
            if (config('app.debug')) {
                return null;
            }

            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Something went wrong',
                    'message' => $e->getMessage(),
                ], $status);
            }

            return response()->view('errors.generic', [
                'message' => $e->getMessage(),
                'status' => $status,
            ], $status);
        });
    })->create();
