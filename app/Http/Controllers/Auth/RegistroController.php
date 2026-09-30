<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Rol;
use App\Models\Usuario;
use App\Servicios\VinculacionClientes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** RF-01: registro de usuarios. Solo se pueden autoregistrar emprendedores y clientes. */
class RegistroController extends Controller
{
    public function __construct(private readonly VinculacionClientes $vinculacion)
    {
    }

    public function create(Request $request): View
    {
        // Si el cliente llega con el enlace de invitación, se muestra el nombre del negocio que lo invitó.
        $codigo = Cliente::normalizarCodigo((string) $request->query('codigo'));
        $invitacion = strlen($codigo) === 8
            ? Cliente::withoutGlobalScopes()->with('emprendedor')->where('codigo_vinculacion', $codigo)->whereNull('id_usuario')->first()
            : null;

        return view('auth.registro', [
            'invitacion' => $invitacion,
            'negocioInvita' => $invitacion ? ($invitacion->emprendedor->negocio ?? $invitacion->emprendedor->nombre) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['correo' => mb_strtolower(trim((string) $request->input('correo')))]);

        $datos = $request->validate([
            'tipo' => ['required', Rule::in(['emprendedor', 'cliente'])],
            'nombre' => ['required', 'string', 'max:100'],
            'negocio' => ['nullable', 'required_if:tipo,emprendedor', 'string', 'max:100'],
            'correo' => ['required', 'string', 'email', 'max:150', Rule::unique('usuarios', 'correo')],
            'contrasena' => ['required', 'confirmed', Password::defaults()],
            'codigo' => ['nullable', 'string', 'max:20'],
        ]);

        $esEmprendedor = $datos['tipo'] === 'emprendedor';
        $codigo = $esEmprendedor ? null : ($datos['codigo'] ?? null);

        // El código se verifica antes de crear la cuenta: si es inválido, el cliente puede corregirlo.
        $cliente = $codigo ? $this->vinculacion->buscar($codigo, 'vincular-registro:'.$request->ip()) : null;

        $usuario = DB::transaction(function () use ($datos, $esEmprendedor, $cliente) {
            $usuario = Usuario::create([
                'nombre' => $datos['nombre'],
                'correo' => $datos['correo'],
                'contrasena' => $datos['contrasena'],
                'id_rol' => $esEmprendedor ? Rol::EMPRENDEDOR : Rol::CLIENTE,
                'negocio' => $esEmprendedor ? $datos['negocio'] : null,
            ]);

            $cliente?->vincularCon($usuario);

            return $usuario;
        });

        Auth::login($usuario);
        $request->session()->regenerate();

        $mensaje = $codigo
            ? '¡Bienvenido! Su cuenta fue creada y vinculada con sus pedidos.'
            : '¡Bienvenido! Su cuenta fue creada correctamente.';

        return redirect()->route('panel')->with('exito', $mensaje);
    }
}
