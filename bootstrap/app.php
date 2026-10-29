<?php

use App\Http\Middleware\VerificarCuentaActiva;
use App\Http\Middleware\VerificarRol;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['rol' => VerificarRol::class]);
        $middleware->web(append: [VerificarCuentaActiva::class]);
        $middleware->redirectUsersTo('/panel');

        // Detrás del proxy del alojamiento (p. ej. Railway): IP real del visitante para los límites de intentos.
        // TRUSTED_PROXIES=* solo donde el sistema no es accesible sin pasar por ese proxy.
        if ($proxies = env('TRUSTED_PROXIES')) {
            $middleware->trustProxies(at: $proxies === '*' ? '*' : explode(',', $proxies));
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
