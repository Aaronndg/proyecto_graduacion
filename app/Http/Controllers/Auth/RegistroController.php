<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** RF-01: registro de usuarios. Solo se pueden autoregistrar emprendedores y clientes. */
class RegistroController extends Controller
{
    public function create(): View
    {
        return view('auth.registro');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['correo' => mb_strtolower(trim((string) $request->input('correo')))]);

        $datos = $request->validate([
            'tipo' => ['required', Rule::in(['emprendedor', 'cliente'])],
            'nombre' => ['required', 'string', 'max:100'],
            'negocio' => ['nullable', 'required_if:tipo,emprendedor', 'string', 'max:100'],
            'correo' => ['required', 'string', 'email', 'max:150', Rule::unique('usuarios', 'correo')],
            'contrasena' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $esEmprendedor = $datos['tipo'] === 'emprendedor';

        $usuario = Usuario::create([
            'nombre' => $datos['nombre'],
            'correo' => $datos['correo'],
            'contrasena' => $datos['contrasena'],
            'id_rol' => $esEmprendedor ? Rol::EMPRENDEDOR : Rol::CLIENTE,
            'negocio' => $esEmprendedor ? $datos['negocio'] : null,
        ]);

        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('panel')->with('exito', '¡Bienvenido! Su cuenta fue creada correctamente.');
    }
}
