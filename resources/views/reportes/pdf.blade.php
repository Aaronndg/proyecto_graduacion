{{-- Reporte en PDF (Dompdf): diseño claro para leer, enviar o imprimir; solo HTML y CSS básicos. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de pedidos y ventas · {{ $negocio }}</title>
    <style>
        @page { margin: 28px 34px 40px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1C1B19; }
        .cabecera { border-bottom: 2px solid #E0B04F; padding-bottom: 8px; margin-bottom: 14px; }
        .marca { font-size: 9px; letter-spacing: 3px; color: #8F6410; font-weight: bold; }
        h1 { font-size: 17px; margin: 2px 0 2px; }
        h2 { font-size: 12px; margin: 16px 0 6px; }
        .meta { color: #5B5850; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1A1B20; color: #FFFFFF; text-align: left; padding: 5px 6px; font-size: 9.5px; }
        td { padding: 4px 6px; border-bottom: 1px solid #E4DFD3; }
        .num { text-align: right; }
        .tarjetas td { width: 33%; border: 1px solid #E4DFD3; padding: 7px 8px; vertical-align: top; }
        .tarjetas .valor { font-size: 15px; font-weight: bold; }
        .tarjetas .rotulo { color: #5B5850; font-size: 9px; }
        .nota { color: #5B5850; font-size: 9px; margin-top: 6px; }
        .pie { position: fixed; bottom: -24px; left: 0; right: 0; font-size: 8.5px; color: #8A867C; }
    </style>
</head>
<body>
    <div class="pie">NEXO · {{ $negocio }} · Generado el {{ now()->format('d/m/Y H:i') }}</div>

    <div class="cabecera">
        <div class="marca">NEXO</div>
        <h1>Reporte de pedidos y ventas</h1>
        <div class="meta">{{ $negocio }} · {{ $periodo }}</div>
    </div>

    <table class="tarjetas">
        <tr>
            <td><div class="rotulo">Pedidos del período</div><div class="valor">{{ $resumen['pedidos'] }}</div></td>
            <td><div class="rotulo">Ventas (entregados)</div><div class="valor">{{ $resumen['ventas'] }}</div></td>
            <td><div class="rotulo">Monto vendido</div><div class="valor">Q {{ number_format($resumen['monto_vendido'], 2) }}</div></td>
        </tr>
        <tr>
            <td><div class="rotulo">En curso</div><div class="valor">{{ $resumen['en_curso'] }}</div></td>
            <td><div class="rotulo">Cancelados</div><div class="valor">{{ $resumen['cancelados'] }}</div></td>
            <td><div class="rotulo">Venta promedio</div><div class="valor">Q {{ number_format($resumen['ticket_promedio'], 2) }}</div></td>
        </tr>
    </table>

    <h2>Pedidos por estado</h2>
    <table>
        <tr><th>Estado</th><th class="num">Pedidos</th></tr>
        @foreach ($porEstado as $fila)
            <tr><td>{{ $fila['estado']->nombre }}</td><td class="num">{{ $fila['cantidad'] }}</td></tr>
        @endforeach
    </table>

    <h2>Productos más vendidos</h2>
    @if ($productos->isEmpty())
        <p class="meta">No hay ventas entregadas en este período.</p>
    @else
        <table>
            <tr><th>Producto</th><th class="num">Unidades</th><th class="num">Monto</th></tr>
            @foreach ($productos as $producto)
                <tr><td>{{ $producto['nombre'] }}</td><td class="num">{{ $producto['unidades'] }}</td><td class="num">Q {{ number_format($producto['monto'], 2) }}</td></tr>
            @endforeach
        </table>
    @endif

    <h2>Detalle de pedidos</h2>
    @if ($filas->isEmpty())
        <p class="meta">No hay pedidos en este período.</p>
    @else
        <table>
            <tr><th>Pedido</th><th>Fecha</th><th>Cliente</th><th>Estado</th><th class="num">Productos</th><th class="num">Total</th></tr>
            @foreach ($filas as $fila)
                <tr>
                    <td>{{ $fila['pedido'] }}</td>
                    <td>{{ $fila['fecha']->format('d/m/Y H:i') }}</td>
                    <td>{{ $fila['cliente'] }}</td>
                    <td>{{ $fila['estado'] }}</td>
                    <td class="num">{{ $fila['unidades'] }}</td>
                    <td class="num">Q {{ number_format($fila['total'], 2) }}</td>
                </tr>
            @endforeach
        </table>
        @if ($recortado)<p class="nota">Se muestran los primeros {{ $filas->count() }} pedidos. Para el detalle completo, descargue el Excel.</p>@endif
    @endif
</body>
</html>
