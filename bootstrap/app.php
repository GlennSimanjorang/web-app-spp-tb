<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
<<<<<<< HEAD
=======
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

>>>>>>> 48ceca89c80cd3cb95c2541bb2833327718bb572

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
<<<<<<< HEAD
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
=======
        $middleware->alias([
            "token-check" => \App\Http\Middleware\TokenCheck::class,
            "role" => \App\Http\Middleware\RolePermission::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            return null;
        });
    })

    ->create();
>>>>>>> 48ceca89c80cd3cb95c2541bb2833327718bb572
