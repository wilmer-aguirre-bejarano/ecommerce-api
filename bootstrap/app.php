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
    ->withMiddleware(function (Middleware $middleware): void {
        //sitio de las Middleware
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class, // Asegúrate de poner la ruta real a tu archivo
        ]);
        //alias significa "apodo". Le estamos diciendo a Laravel: "cuando en una ruta escriba admin, ejecuta la clase IsAdmin". Es como un import con nombre corto.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
