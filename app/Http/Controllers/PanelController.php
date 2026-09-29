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

    private function emprendedor(): View
    {
        // El trait PerteneceAEmprendedor limita estas consultas a los datos del emprendedor autenticado.
        return view('panel.emprendedor', [
            'totalClientes' => Cliente::count(),
            'totalProductos' => Producto::activos()->count(),
            'pedidosPendientes' => Pedido::whereNotIn('id_estado', EstadoPedido::FINALES)->count(),
            'pedidosRecientes' => Pedido::with(['cliente', 'estado'])->latest('fecha')->latest('id_pedido')->take(5)->get(),
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
