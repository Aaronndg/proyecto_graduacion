<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as ReglaContrasena;
use Illuminate\View\View;

/** «¿Olvidó su contraseña?»: pedir el enlace por correo y crear la contraseña nueva. */
class ContrasenaOlvidadaController extends Controller
{
    public function create(): View
    {
        return view('auth.olvido');
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate(['correo' => ['required', 'string', 'email']]);

        // La respuesta es la misma exista o no la cuenta: así nadie puede averiguar qué correos están registrados.
        Password::sendResetLink(['correo' => mb_strtolower(trim($datos['correo']))]);

        return back()->with('enviado', $datos['correo']);
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.contrasena-nueva', ['token' => $token, 'correo' => (string) $request->query('correo')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'token' => ['required', 'string'],
            'correo' => ['required', 'string', 'email'],
            'contrasena' => ['required', 'confirmed', ReglaContrasena::defaults()],
        ], [], ['contrasena' => 'contraseña']);

        $estado = Password::reset(
            ['correo' => mb_strtolower(trim($datos['correo'])), 'token' => $datos['token'], 'password' => $datos['contrasena']],
            function (Usuario $usuario, string $contrasena) {
                $usuario->forceFill(['contrasena' => $contrasena])->setRememberToken(null);
                $usuario->save();
            }
        );

        if ($estado !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('correo'))
                ->withErrors(['correo' => 'El enlace ya no sirve (vence a los 60 minutos o ya se usó). Pida uno nuevo.']);
        }

        return redirect()->route('login')->with('exito', 'Listo. Ya puede entrar con su contraseña nueva.');
    }
}
