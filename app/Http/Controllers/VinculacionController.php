<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Servicios\VinculacionClientes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VinculacionController extends Controller
{
    /** El cliente ingresa el código que le entregó el emprendedor para ver sus pedidos. */
    public function store(Request $request, VinculacionClientes $vinculacion): RedirectResponse
    {
        $datos = $request->validate(['codigo' => ['required', 'string', 'max:20']]);

        $cliente = $vinculacion->vincular($request->user(), $datos['codigo']);
        $negocio = $cliente->emprendedor->negocio ?? $cliente->emprendedor->nombre;

        return redirect()->route('panel')->with('exito', "¡Listo! Ya puede ver sus pedidos de {$negocio}.");
    }

    /** El emprendedor desvincula la cuenta actual (si la hay) y genera un código nuevo. */
    public function regenerar(Cliente $cliente): RedirectResponse
    {
        $teniaCuenta = $cliente->id_usuario !== null;
        $cliente->regenerarCodigo();

        return back()->with('exito', $teniaCuenta
            ? 'Cuenta desconectada. Si el cliente quiere volver a ver sus pedidos, envíele la nueva invitación.'
            : 'Código nuevo creado. El anterior ya no funciona.');
    }
}
