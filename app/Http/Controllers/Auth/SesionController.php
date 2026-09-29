<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** RF-02 inicio de sesión y RF-16 cierre de sesión. */
class SesionController extends Controller
{
    private const INTENTOS_MAXIMOS = 5;

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'correo' => ['required', 'string', 'email'],
            'contrasena' => ['required', 'string'],
        ]);

        // Limita los intentos fallidos para dificultar ataques de fuerza bruta.
        $clave = Str::lower($datos['correo']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($clave, self::INTENTOS_MAXIMOS)) {
            throw ValidationException::withMessages([
                'correo' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($clave)]),
            ]);
        }

        if (! Auth::attempt(['correo' => $datos['correo'], 'password' => $datos['contrasena']])) {
            RateLimiter::hit($clave, 60);

            throw ValidationException::withMessages(['correo' => __('auth.failed')]);
        }

        RateLimiter::clear($clave);

        if (! Auth::user()->activo) {
            Auth::logout();

            throw ValidationException::withMessages(['correo' => __('auth.inactive')]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('panel'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('exito', 'Sesión cerrada correctamente.');
    }
}
