<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** actualizarPerfil() del diagrama de clases (Figura 32). */
class PerfilController extends Controller
{
    public function edit(Request $request): View
    {
        return view('perfil.editar', ['usuario' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $usuario = $request->user();
        $request->merge(['correo' => mb_strtolower(trim((string) $request->input('correo')))]);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'string', 'email', 'max:150', Rule::unique('usuarios', 'correo')->ignore($usuario->id_usuario, 'id_usuario')],
            'negocio' => [Rule::requiredIf($usuario->esEmprendedor()), 'nullable', 'string', 'max:100'],
        ]);

        if (! $usuario->esEmprendedor()) {
            unset($datos['negocio']);
        }

        $usuario->update($datos);

        return back()->with('exito', 'Perfil actualizado correctamente.');
    }

    public function actualizarContrasena(Request $request): RedirectResponse
    {
        $datos = $request->validateWithBag('contrasena', [
            'contrasena_actual' => ['required', 'current_password'],
            'contrasena' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['contrasena' => $datos['contrasena']]);

        return back()->with('exito', 'Contraseña actualizada correctamente.');
    }
}
