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
        // Append Prometheus middleware only if the class is available to avoid undefined type errors
        $prometheus = 'Iamfarhad\\LaravelPrometheus\\Http\\Middleware\\PrometheusMiddleware';
        if (class_exists($prometheus)) {
            $middleware->appendToGroup('web', $prometheus);
            $middleware->appendToGroup('api', $prometheus);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
