<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReporteRequest;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** MOD-05 / HU-05 (Figura 41): reportes de pedidos y ventas del emprendedor. */
class ReporteController extends Controller
{
    public function index(ReporteRequest $request): View
    {
        $reportes = $request->reportes();
        $anterior = $reportes->anterior();

        return view('reportes.index', [
            'resumen' => $reportes->resumen(),
            // Comparación con el período anterior de la misma duración
            'anterior' => $anterior->resumen() + ['desde' => $anterior->desde(), 'hasta' => $anterior->hasta()],
            'porEstado' => $reportes->porEstado(),
            'ventas' => $reportes->ventasPorPeriodo(),
            'porMes' => $reportes->agrupaPorMes(),
            'productos' => $reportes->productosMasVendidos(),
            'pedidos' => $reportes->pedidos()
                ->with(['cliente', 'estado'])
                ->withSum('detalles as unidades', 'cantidad')
                ->latest('fecha')
                ->latest('id_pedido')
                ->paginate(10)
                ->withQueryString(),
            'estados' => EstadoPedido::orderBy('orden')->get(),
            'filtros' => [
                'desde' => $reportes->desde()->toDateString(),
                'hasta' => $reportes->hasta()->toDateString(),
                'estado' => $request->integer('estado') ?: null,
            ],
        ]);
    }

    /** Descarga del detalle en CSV (se abre directamente en Excel). */
    public function exportar(ReporteRequest $request): StreamedResponse
    {
        $reportes = $request->reportes();
        $nombre = sprintf('reporte-pedidos_%s_%s.csv', $reportes->desde()->format('Ymd'), $reportes->hasta()->format('Ymd'));

        return response()->streamDownload(function () use ($reportes) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF"); // BOM: Excel reconoce las tildes y la ñ.
            fputcsv($salida, ['Pedido', 'Fecha', 'Cliente', 'Estado', 'Productos', 'Total (Q)']);

            $reportes->pedidos()
                ->with(['cliente', 'estado'])
                ->withSum('detalles as unidades', 'cantidad')
                ->orderBy('fecha')
                ->orderBy('id_pedido')
                ->chunk(200, function ($pedidos) use ($salida) {
                    foreach ($pedidos as $pedido) {
                        /** @var Pedido $pedido */
                        fputcsv($salida, [
                            '#'.$pedido->numero(),
                            $pedido->fecha->format('d/m/Y H:i'),
                            self::celdaSegura($pedido->cliente->nombre),
                            $pedido->estado->nombre,
                            (int) $pedido->unidades,
                            number_format((float) $pedido->total, 2, '.', ''),
                        ]);
                    }
                });

            fclose($salida);
        }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Evita que Excel interprete como fórmula un texto escrito por el usuario (inyección CSV). */
    private static function celdaSegura(string $texto): string
    {
        return preg_match('/^[=+\-@\t\r]/', $texto) ? "'".$texto : $texto;
    }
}
