<?php

namespace App\Servicios;

use App\Models\DetallePedido;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * MOD-05 / RF-13, RF-14: información consolidada de pedidos y ventas de un período.
 * Una venta es un pedido entregado; se ubica en el período por la fecha del pedido.
 * Las consultas pasan por el trait PerteneceAEmprendedor, así que solo incluyen datos propios.
 */
class Reportes
{
    /** Por encima de este número de días, las ventas se agrupan por mes en lugar de por día. */
    private const MAX_DIAS_AGRUPADOS = 62;

    public function __construct(
        private readonly Carbon $desde,
        private readonly Carbon $hasta,
        private readonly ?int $idEstado = null,
    ) {
    }

    /** Pedidos del período, con el filtro de estado si se indicó. */
    public function pedidos(): Builder
    {
        return $this->delPeriodo()->when($this->idEstado, fn ($q) => $q->where('id_estado', $this->idEstado));
    }

    /** Totales de las tarjetas de resumen. */
    public function resumen(): array
    {
        $porEstado = $this->delPeriodo()
            ->selectRaw('id_estado, count(*) as cantidad, sum(total) as monto')
            ->groupBy('id_estado')
            ->get()
            ->keyBy('id_estado');

        $entregados = $porEstado->get(EstadoPedido::ENTREGADO);
        $ventas = (int) ($entregados->cantidad ?? 0);
        $monto = (float) ($entregados->monto ?? 0);

        return [
            'pedidos' => (int) $porEstado->sum('cantidad'),
            'ventas' => $ventas,
            'en_curso' => (int) $porEstado->whereNotIn('id_estado', EstadoPedido::FINALES)->sum('cantidad'),
            'cancelados' => (int) ($porEstado->get(EstadoPedido::CANCELADO)->cantidad ?? 0),
            'monto_vendido' => $monto,
            'ticket_promedio' => $ventas ? $monto / $ventas : 0.0,
        ];
    }

    /** Cantidad de pedidos del período en cada estado (todos los estados, aunque tengan cero). */
    public function porEstado(): Collection
    {
        $conteo = $this->delPeriodo()->selectRaw('id_estado, count(*) as cantidad')->groupBy('id_estado')->pluck('cantidad', 'id_estado');

        return EstadoPedido::orderBy('orden')->get()->map(fn (EstadoPedido $estado) => [
            'estado' => $estado,
            'cantidad' => (int) ($conteo[$estado->id_estado] ?? 0),
        ]);
    }

    /**
     * Monto vendido por día (o por mes en períodos largos), incluyendo los días sin ventas
     * para que la gráfica no oculte los huecos.
     *
     * @return Collection<int, array{etiqueta: string, detalle: string, monto: float, ventas: int}>
     */
    public function ventasPorPeriodo(): Collection
    {
        $porMes = $this->agrupaPorMes();
        $clave = fn (Carbon $fecha) => $fecha->format($porMes ? 'Y-m' : 'Y-m-d');

        $ventas = $this->delPeriodo()
            ->where('id_estado', EstadoPedido::ENTREGADO)
            ->get(['fecha', 'total'])
            ->groupBy(fn (Pedido $p) => $clave($p->fecha));

        $periodo = $porMes
            ? CarbonPeriod::create($this->desde->copy()->startOfMonth(), '1 month', $this->hasta->copy()->startOfMonth())
            : CarbonPeriod::create($this->desde->copy()->startOfDay(), '1 day', $this->hasta->copy()->startOfDay());

        return collect($periodo)->map(function (Carbon $fecha) use ($ventas, $clave, $porMes) {
            $grupo = $ventas->get($clave($fecha), collect());

            return [
                'etiqueta' => $porMes ? ucfirst($fecha->translatedFormat('M y')) : $fecha->format('d/m'),
                'detalle' => $porMes ? ucfirst($fecha->translatedFormat('F Y')) : ucfirst($fecha->translatedFormat('l d/m/Y')),
                'monto' => round((float) $grupo->sum('total'), 2),
                'ventas' => $grupo->count(),
            ];
        })->values();
    }

    /** Productos más vendidos (en pedidos entregados) por monto. */
    public function productosMasVendidos(int $limite = 5): Collection
    {
        return DetallePedido::query()
            ->join('productos', 'productos.id_producto', '=', 'detalle_pedido.id_producto')
            ->whereIn('detalle_pedido.id_pedido', $this->delPeriodo()->where('id_estado', EstadoPedido::ENTREGADO)->select('id_pedido'))
            ->groupBy('detalle_pedido.id_producto', 'productos.nombre')
            ->selectRaw('productos.nombre, sum(detalle_pedido.cantidad) as unidades, sum(detalle_pedido.subtotal) as monto')
            ->orderByDesc('monto')
            ->orderBy('productos.nombre')
            ->limit($limite)
            ->get()
            ->map(fn ($fila) => ['nombre' => $fila->nombre, 'unidades' => (int) $fila->unidades, 'monto' => (float) $fila->monto]);
    }

    public function agrupaPorMes(): bool
    {
        return $this->desde->diffInDays($this->hasta) > self::MAX_DIAS_AGRUPADOS;
    }

    public function desde(): Carbon
    {
        return $this->desde;
    }

    public function hasta(): Carbon
    {
        return $this->hasta;
    }

    private function delPeriodo(): Builder
    {
        return Pedido::query()->whereBetween('fecha', [$this->desde->copy()->startOfDay(), $this->hasta->copy()->endOfDay()]);
    }
}
