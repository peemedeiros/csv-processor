<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('jwt', \App\Http\Middleware\JwtMiddleware::class);
        $middleware->api(\App\Http\Middleware\ApiLogMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            throw new \App\Exceptions\ResourceNotFoundException();
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e) {
            throw new \App\Exceptions\FormValidationException($e);
        });

        $exceptions->render(function (\Illuminate\Database\QueryException $e) {
            throw new \App\Exceptions\DatabasePersistenceException(sqlErrorMessage: $e->getMessage());
        });

        $exceptions->render(function (Exception|TypeError $e) {
            throw new \App\Exceptions\UnhandledException($e->getMessage(), 500);
        });
    })->create();
