<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClienteRequest;
use App\Http\Requests\ProductoRequest;
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
            // Del negocio (solo emprendedor): WhatsApp para que los clientes le escriban y su logo.
            'telefono' => ['nullable', 'string', 'max:20', ClienteRequest::TELEFONO],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.ProductoRequest::IMAGEN_MAX_KB],
            'quitar_logo' => ['nullable', 'boolean'],
        ], [
            'telefono.regex' => 'El teléfono solo puede contener números, espacios, guiones o el signo +, con al menos 8 dígitos.',
            'logo.image' => 'El archivo debe ser una imagen.',
            'logo.mimes' => 'El logo debe ser JPG, PNG o WebP.',
            'logo.max' => 'El logo no debe pesar más de 2 MB.',
        ]);

        unset($datos['logo'], $datos['quitar_logo']);
        if ($usuario->esEmprendedor()) {
            $usuario->cambiarLogo($request->file('logo'), $request->boolean('quitar_logo'));
        } else {
            unset($datos['negocio'], $datos['telefono']);
        }

        $usuario->fill($datos)->save();

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
