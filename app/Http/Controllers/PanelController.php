<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Panel principal (Figura 35): muestra un resumen distinto según el rol del usuario. */
class PanelController extends Controller
{
    public function __invoke(Request $request): View
    {
        $usuario = $request->user();

        return match (true) {
            $usuario->esAdministrador() => $this->administrador(),
            $usuario->esEmprendedor() => $this->emprendedor(),
            default => $this->cliente($usuario),
        };
    }

    private function administrador(): View
    {
        $porRol = Usuario::selectRaw('id_rol, count(*) as total')->groupBy('id_rol')->pluck('total', 'id_rol');

        return view('panel.administrador', [
            'totalEmprendedores' => $porRol[Rol::EMPRENDEDOR] ?? 0,
            'totalClientes' => $porRol[Rol::CLIENTE] ?? 0,
            'totalInactivos' => Usuario::where('activo', false)->count(),
            'totalPedidos' => Pedido::count(),
            'usuariosRecientes' => Usuario::with('rol')->latest()->take(5)->get(),
        ]);
    }

    /** Pedidos que se muestran por columna en «Hoy»; el resto se ve en Pedidos con el filtro del estado. */
    private const POR_COLUMNA = 8;

    /**
     * «Hoy» (unión de Inicio y Seguimiento, MOD-04): los pedidos activos por etapa, los más antiguos
     * primero, con la siguiente acción, y un resumen de lo pendiente y de las ventas de 7 días.
     */
    private function emprendedor(): View
    {
        // El trait PerteneceAEmprendedor limita estas consultas a los datos del emprendedor autenticado.
        // detalles.producto: miniaturas de lo que pidió cada cliente (fotos de producto).
        $activos = Pedido::with(['cliente', 'detalles.producto'])
            ->withSum('detalles as unidades', 'cantidad')
            ->whereIn('id_estado', EstadoPedido::ACTIVOS)
            ->orderByRaw('fecha_entrega is null')
            ->orderBy('fecha_entrega')
            ->orderBy('fecha')
            ->orderBy('id_pedido')
            ->get()
            ->groupBy('id_estado');

        $estados = EstadoPedido::whereIn('id_estado', [...EstadoPedido::ACTIVOS, EstadoPedido::ENTREGADO])->get()->keyBy('id_estado');

        // Venta = pedido entregado (mismo criterio que Reportes), ubicada por la fecha del pedido.
        $desde = today()->subDays(6);
        $ventas = Pedido::where('id_estado', EstadoPedido::ENTREGADO)
            ->where('fecha', '>=', $desde)
            ->selectRaw('count(*) as entregas, coalesce(sum(total), 0) as monto')
            ->first();

        return view('panel.emprendedor', [
            'columnas' => collect(EstadoPedido::ACTIVOS)->map(fn (int $id) => [
                'estado' => $estados[$id],
                'pedidos' => $activos->get($id, collect())->take(self::POR_COLUMNA),
                'total' => $activos->get($id, collect())->count(),
                // Siguiente paso natural del flujo (Nuevo → En proceso → Listo → Entregado).
                'siguiente' => $estados[$id + 1],
            ]),
            'porAtender' => $activos->sum(fn ($grupo) => $grupo->count()),
            'ventas' => ['entregas' => (int) $ventas->entregas, 'monto' => (float) $ventas->monto, 'desde' => $desde],
            'tieneProductos' => Producto::exists(),
        ]);
    }

    private function cliente(Usuario $usuario): View
    {
        // RN-06: el cliente solo consulta los pedidos asociados a su cuenta.
        $pedidos = Pedido::with(['estado', 'emprendedor'])
            ->whereIn('id_cliente', $usuario->registrosCliente()->select('id_cliente'))
            ->latest('fecha')
            ->latest('id_pedido')
            ->get();

        return view('panel.cliente', [
            'pedidos' => $pedidos,
            'enCurso' => $pedidos->whereNotIn('id_estado', EstadoPedido::FINALES)->count(),
        ]);
    }
}
