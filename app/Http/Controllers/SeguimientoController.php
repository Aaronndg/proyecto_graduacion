<?php

namespace App\Http\Controllers;

use App\Models\EstadoPedido;
use App\Models\Pedido;
use Illuminate\View\View;

/** MOD-04: tablero de seguimiento de los pedidos activos del emprendedor. */
class SeguimientoController extends Controller
{
    private const COLUMNAS = [EstadoPedido::NUEVO, EstadoPedido::EN_PROCESO, EstadoPedido::LISTO];

    public function __invoke(): View
    {
        $estados = EstadoPedido::whereIn('id_estado', self::COLUMNAS)->orderBy('orden')->get();
        $siguientes = EstadoPedido::whereIn('id_estado', [EstadoPedido::EN_PROCESO, EstadoPedido::LISTO, EstadoPedido::ENTREGADO])
            ->get()->keyBy('id_estado');

        $pedidos = Pedido::with('cliente')
            ->withSum('detalles as unidades', 'cantidad')
            ->whereIn('id_estado', self::COLUMNAS)
            ->orderBy('fecha')
            ->get()
            ->groupBy('id_estado');

        return view('seguimiento.index', [
            'columnas' => $estados->map(fn (EstadoPedido $estado) => [
                'estado' => $estado,
                'pedidos' => $pedidos->get($estado->id_estado, collect()),
                // Siguiente paso natural del flujo (Nuevo → En proceso → Listo → Entregado).
                'siguiente' => $siguientes->get($estado->id_estado + 1),
            ]),
        ]);
    }
}
