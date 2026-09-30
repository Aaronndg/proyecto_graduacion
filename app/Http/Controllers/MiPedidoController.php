<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** RF-10 / HU-04: el cliente consulta el estado de sus propios pedidos (solo lectura, RN-06). */
class MiPedidoController extends Controller
{
    public function show(Request $request, Pedido $pedido): View
    {
        $pedido->load(['cliente', 'estado', 'emprendedor', 'detalles.producto', 'historial.estado']);

        // Un pedido que no pertenece al cliente se trata como inexistente (no revela que existe).
        abort_unless($pedido->cliente->id_usuario === $request->user()->id_usuario, 404);

        return view('mis-pedidos.show', compact('pedido'));
    }
}
