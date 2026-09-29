<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * RF-04: el administrador consulta y administra los usuarios registrados.
 * Las cuentas no se eliminan (se conserva el historial de pedidos); se desactivan.
 */
class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar'));
        $rol = $request->integer('rol') ?: null;

        $usuarios = Usuario::with('rol')
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'like', "%{$buscar}%")
                ->orWhere('correo', 'like', "%{$buscar}%")
                ->orWhere('negocio', 'like', "%{$buscar}%")))
            ->when($rol, fn ($q) => $q->where('id_rol', $rol))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'roles' => Rol::orderBy('id_rol')->get(),
            'buscar' => $buscar,
            'rol' => $rol,
        ]);
    }

    public function create(): View
    {
        return view('admin.usuarios.formulario', [
            'usuario' => new Usuario(['activo' => true, 'id_rol' => Rol::EMPRENDEDOR]),
            'roles' => Rol::orderBy('id_rol')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        Usuario::create($datos);

        return redirect()->route('admin.usuarios.index')->with('exito', 'Usuario registrado correctamente.');
    }

    public function edit(Usuario $usuario): View
    {
        return view('admin.usuarios.formulario', [
            'usuario' => $usuario,
            'roles' => Rol::orderBy('id_rol')->get(),
        ]);
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        $datos = $this->validar($request, $usuario);

        // El administrador no puede quitarse su propio rol ni desactivarse (evita quedar sin acceso).
        if ($usuario->is($request->user())) {
            $datos['id_rol'] = $usuario->id_rol;
            $datos['activo'] = true;
        }

        if (empty($datos['contrasena'])) {
            unset($datos['contrasena']);
        }

        $usuario->update($datos);

        return redirect()->route('admin.usuarios.index')->with('exito', 'Usuario actualizado correctamente.');
    }

    public function alternarEstado(Request $request, Usuario $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No puede desactivar su propia cuenta.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        return back()->with('exito', $usuario->activo ? 'Cuenta activada.' : 'Cuenta desactivada.');
    }

    private function validar(Request $request, ?Usuario $usuario = null): array
    {
        $request->merge([
            'correo' => mb_strtolower(trim((string) $request->input('correo'))),
            'activo' => $request->boolean('activo'),
        ]);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'string', 'email', 'max:150', Rule::unique('usuarios', 'correo')->ignore($usuario?->id_usuario, 'id_usuario')],
            'id_rol' => ['required', 'integer', Rule::exists('roles', 'id_rol')],
            'negocio' => ['nullable', Rule::requiredIf((int) $request->input('id_rol') === Rol::EMPRENDEDOR), 'string', 'max:100'],
            'activo' => ['boolean'],
            'contrasena' => [$usuario ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        if ((int) $datos['id_rol'] !== Rol::EMPRENDEDOR) {
            $datos['negocio'] = null;
        }

        return $datos;
    }
}
