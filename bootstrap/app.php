<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'patient.bearer' => \App\Http\Middleware\EnsureBearerRole::class,
            'doctor.bearer' => \App\Http\Middleware\EnsureBearerRole::class,
            'admin.bearer' => \App\Http\Middleware\EnsureBearerRole::class,
            'secretary.bearer' => \App\Http\Middleware\EnsureBearerRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
