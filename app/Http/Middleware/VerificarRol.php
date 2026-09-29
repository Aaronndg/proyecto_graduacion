<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RF-03 / RN-09: restringe una ruta a los roles indicados. Uso: ->middleware('rol:administrador,emprendedor').
 * La verificación ocurre en el servidor, no solo ocultando opciones en la interfaz (5.6.1).
 */
class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        abort_unless($request->user()?->tieneRol(...$roles), 403);

        return $next($request);
    }
}
