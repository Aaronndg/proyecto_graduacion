<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReporteRequest;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use App\Servicios\LibroReporte;
use App\Servicios\Reportes;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\LazyCollection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** MOD-05 / HU-05 (Figura 41): reportes de pedidos y ventas del emprendedor. */
class ReporteController extends Controller
{
    /** Límite de filas del detalle en el PDF, para que el documento no crezca sin control. */
    private const MAX_FILAS_PDF = 500;

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

    /** Descarga del detalle en CSV (texto separado por comas; también se abre en Excel). */
    public function exportar(ReporteRequest $request): StreamedResponse
    {
        $reportes = $request->reportes();

        return response()->streamDownload(function () use ($reportes) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF"); // BOM: Excel reconoce las tildes y la ñ.
            fputcsv($salida, ['Pedido', 'Fecha', 'Cliente', 'Estado', 'Productos', 'Total (Q)']);

            foreach ($this->filasDetalle($reportes) as $fila) {
                fputcsv($salida, [
                    $fila['pedido'],
                    $fila['fecha']->format('d/m/Y H:i'),
                    self::celdaSegura($fila['cliente']),
                    $fila['estado'],
                    $fila['unidades'],
                    number_format($fila['total'], 2, '.', ''),
                ]);
            }

            fclose($salida);
        }, $this->nombreArchivo($reportes, 'csv'), ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Libro de Excel (.xlsx) con tres hojas: resumen, detalle de pedidos y productos más vendidos. */
    public function excel(ReporteRequest $request): StreamedResponse
    {
        $reportes = $request->reportes();

        return response()->streamDownload(function () use ($reportes) {
            (new LibroReporte)->escribir(
                'php://output',
                auth()->user()->nombreNegocio(),
                $this->periodo($reportes),
                $reportes->resumen(),
                $reportes->porEstado(),
                $reportes->productosMasVendidos(20),
                $this->filasDetalle($reportes),
            );
        }, $this->nombreArchivo($reportes, 'xlsx'), ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /** Documento PDF del reporte, listo para guardar, enviar o imprimir. */
    public function pdf(ReporteRequest $request): Response
    {
        $reportes = $request->reportes();
        $filas = $this->filasDetalle($reportes)->take(self::MAX_FILAS_PDF + 1)->collect();

        return Pdf::loadView('reportes.pdf', [
            'negocio' => auth()->user()->nombreNegocio(),
            'periodo' => $this->periodo($reportes),
            'resumen' => $reportes->resumen(),
            'porEstado' => $reportes->porEstado(),
            'productos' => $reportes->productosMasVendidos(),
            'filas' => $filas->take(self::MAX_FILAS_PDF),
            'recortado' => $filas->count() > self::MAX_FILAS_PDF,
        ])->setPaper('letter')->setOption('isFontSubsettingEnabled', true)->download($this->nombreArchivo($reportes, 'pdf'));
    }

    /**
     * Pedidos del período (con el filtro de estado) en orden cronológico, listos para exportar.
     *
     * @return LazyCollection<int, array{pedido: string, fecha: Carbon, cliente: string, estado: string, id_estado: int, unidades: int, total: float}>
     */
    private function filasDetalle(Reportes $reportes): LazyCollection
    {
        return $reportes->pedidos()
            ->with(['cliente', 'estado'])
            ->withSum('detalles as unidades', 'cantidad')
            ->orderBy('fecha')
            ->orderBy('id_pedido')
            ->lazy(200)
            ->map(fn (Pedido $pedido) => [
                'pedido' => '#'.$pedido->numero(),
                'fecha' => $pedido->fecha,
                'cliente' => $pedido->cliente->nombre,
                'estado' => $pedido->estado->nombre,
                'id_estado' => $pedido->id_estado,
                'unidades' => (int) $pedido->unidades,
                'total' => (float) $pedido->total,
            ]);
    }

    private function nombreArchivo(Reportes $reportes, string $extension): string
    {
        return sprintf('reporte-pedidos_%s_%s.%s', $reportes->desde()->format('Ymd'), $reportes->hasta()->format('Ymd'), $extension);
    }

    private function periodo(Reportes $reportes): string
    {
        return 'Del '.$reportes->desde()->format('d/m/Y').' al '.$reportes->hasta()->format('d/m/Y');
    }

    /** Evita que Excel interprete como fórmula un texto escrito por el usuario (inyección CSV). */
    private static function celdaSegura(string $texto): string
    {
        return preg_match('/^[=+\-@\t\r]/', $texto) ? "'".$texto : $texto;
    }
}
