<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Cierra la sesión de inmediato si el administrador desactivó la cuenta mientras estaba en uso. */
class VerificarCuentaActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['correo' => __('auth.inactive')]);
        }

        return $next($request);
    }
}
