{{-- Reporte en PDF (Dompdf): diseño claro con la identidad de NEXO para leer, enviar o imprimir; solo HTML y CSS básicos. --}}
@php
    // Fondo y texto de cada estado, iguales a los de la aplicación y del Excel
    $colores = [
        \App\Models\EstadoPedido::NUEVO => ['#DCEBFB', '#1D4E89', '#3B82F6'],
        \App\Models\EstadoPedido::EN_PROCESO => ['#FCEFD6', '#8A5A00', '#E0A030'],
        \App\Models\EstadoPedido::LISTO => ['#DDF3E6', '#1F6B3D', '#2E9E5B'],
        \App\Models\EstadoPedido::ENTREGADO => ['#ECEAE4', '#4A4740', '#8A867C'],
        \App\Models\EstadoPedido::CANCELADO => ['#FBE0DE', '#9B2C24', '#D9534F'],
    ];
    $totalPedidos = max(1, $resumen['pedidos']);
    $maxProducto = max(1, (float) $productos->max('monto'));
    $sumaUnidades = $filas->sum('unidades');
    $sumaTotal = $filas->sum('total');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de pedidos y ventas · {{ $negocio }}</title>
    <style>
        @page { margin: 30px 0 46px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #1C1B19; margin: 0; }
        .contenido { padding: 0 34px; }
        .banda { margin-top: -30px; background: #1A1B20; color: #FFFFFF; padding: 20px 34px 16px; border-bottom: 4px solid #E0B04F; }
        .banda .marca { font-size: 9px; letter-spacing: 4px; color: #E0B04F; font-weight: bold; }
        .banda h1 { font-size: 19px; margin: 3px 0 4px; }
        .banda .meta { color: #D6D2C6; font-size: 10px; }
        .banda .meta b { color: #FFFFFF; }
        h2 { font-size: 11.5px; margin: 18px 0 7px; padding-bottom: 4px; border-bottom: 1px solid #E0B04F; }
        h2 span { color: #8F6410; font-weight: normal; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1A1B20; color: #FFFFFF; text-align: left; padding: 6px 7px; font-size: 8.5px; border-bottom: 2px solid #E0B04F; }
        td { padding: 5px 7px; border-bottom: 1px solid #E4DFD3; }
        tr.par td { background: #F7F5F0; }
        tr.total td { background: #FBF3E1; border-top: 2px solid #E0B04F; border-bottom: none; font-weight: bold; }
        .num { text-align: right; }
        .tarjetas { margin-top: 16px; border-collapse: separate; border-spacing: 6px 6px; margin-left: -6px; width: 102%; }
        .tarjetas td { width: 33%; border: 1px solid #E4DFD3; border-top: 3px solid #E0B04F; padding: 8px 10px; vertical-align: top; background: #FFFFFF; }
        .tarjetas td.destacada { background: #1A1B20; border-color: #1A1B20; border-top-color: #E0B04F; color: #FFFFFF; }
        .tarjetas td.destacada .rotulo { color: #E0B04F; }
        .tarjetas .valor { font-size: 16px; font-weight: bold; margin-top: 2px; }
        .tarjetas .rotulo { color: #5B5850; font-size: 8.5px; text-transform: uppercase; letter-spacing: 1px; }
        .insignia { padding: 2px 7px; border-radius: 8px; font-weight: bold; font-size: 8.5px; }
        .pista { background: #EFECE4; height: 7px; border-radius: 4px; }
        .barra { height: 7px; border-radius: 4px; }
        .rango { color: #8F6410; font-weight: bold; text-align: center; width: 18px; }
        .pedido { color: #8F6410; font-weight: bold; }
        .vacio { color: #5B5850; font-style: italic; }
        .nota { color: #5B5850; font-size: 8.5px; margin-top: 6px; }
        .pie { position: fixed; bottom: -32px; left: 34px; right: 34px; font-size: 8px; color: #8A867C; border-top: 1px solid #E4DFD3; padding-top: 5px; }
        .pie .derecha { float: right; }
    </style>
</head>
<body>
    <div class="pie"><span class="derecha">Solo los pedidos entregados cuentan como venta.</span>NEXO · {{ $negocio }} · Generado el {{ now()->format('d/m/Y H:i') }}</div>

    <div class="banda">
        <div class="marca">NEXO</div>
        <h1>Reporte de pedidos y ventas</h1>
        <div class="meta"><b>{{ $negocio }}</b> · {{ $periodo }}</div>
    </div>

    <div class="contenido">
        <table class="tarjetas">
            <tr>
                <td class="destacada"><div class="rotulo">Monto vendido</div><div class="valor">Q {{ number_format($resumen['monto_vendido'], 2) }}</div></td>
                <td><div class="rotulo">Pedidos del período</div><div class="valor">{{ $resumen['pedidos'] }}</div></td>
                <td><div class="rotulo">Ventas (entregados)</div><div class="valor">{{ $resumen['ventas'] }}</div></td>
            </tr>
            <tr>
                <td><div class="rotulo">Venta promedio</div><div class="valor">Q {{ number_format($resumen['ticket_promedio'], 2) }}</div></td>
                <td><div class="rotulo">En curso</div><div class="valor">{{ $resumen['en_curso'] }}</div></td>
                <td><div class="rotulo">Cancelados</div><div class="valor">{{ $resumen['cancelados'] }}</div></td>
            </tr>
        </table>

        <h2>Pedidos por estado</h2>
        <table>
            <tr><th style="width: 22%">Estado</th><th class="num" style="width: 12%">Pedidos</th><th class="num" style="width: 12%">%</th><th></th></tr>
            @foreach ($porEstado as $i => $fila)
                @php [$fondo, $texto, $barra] = $colores[$fila['estado']->id_estado] ?? ['#FFFFFF', '#1C1B19', '#8A867C']; $parte = $fila['cantidad'] / $totalPedidos; @endphp
                <tr class="{{ $i % 2 ? 'par' : '' }}">
                    <td><span class="insignia" style="background: {{ $fondo }}; color: {{ $texto }}">{{ $fila['estado']->nombre }}</span></td>
                    <td class="num">{{ $fila['cantidad'] }}</td>
                    <td class="num">{{ number_format($parte * 100, 1) }} %</td>
                    <td><div class="pista"><div class="barra" style="width: {{ max(0, round($parte * 100)) }}%; background: {{ $barra }}"></div></div></td>
                </tr>
            @endforeach
        </table>

        <h2>Productos más vendidos <span>· pedidos entregados</span></h2>
        @if ($productos->isEmpty())
            <p class="vacio">No hay ventas entregadas en este período.</p>
        @else
            <table>
                <tr><th style="width: 18px">#</th><th style="width: 32%">Producto</th><th class="num" style="width: 11%">Unidades</th><th class="num" style="width: 15%">Monto</th><th></th></tr>
                @foreach ($productos->values() as $i => $producto)
                    <tr class="{{ $i % 2 ? 'par' : '' }}">
                        <td class="rango">{{ $i + 1 }}</td>
                        <td>{{ $producto['nombre'] }}</td>
                        <td class="num">{{ $producto['unidades'] }}</td>
                        <td class="num">Q {{ number_format($producto['monto'], 2) }}</td>
                        <td><div class="pista"><div class="barra" style="width: {{ round($producto['monto'] / $maxProducto * 100) }}%; background: #E0B04F"></div></div></td>
                    </tr>
                @endforeach
            </table>
        @endif

        <h2>Detalle de pedidos</h2>
        @if ($filas->isEmpty())
            <p class="vacio">No hay pedidos en este período.</p>
        @else
            <table>
                <thead><tr><th>Pedido</th><th>Fecha</th><th>Cliente</th><th>Estado</th><th class="num">Productos</th><th class="num">Total</th></tr></thead>
                @foreach ($filas->values() as $i => $fila)
                    @php [$fondo, $texto] = $colores[$fila['id_estado'] ?? 0] ?? ['#FFFFFF', '#1C1B19']; @endphp
                    <tr class="{{ $i % 2 ? 'par' : '' }}">
                        <td class="pedido">{{ $fila['pedido'] }}</td>
                        <td>{{ $fila['fecha']->format('d/m/Y H:i') }}</td>
                        <td>{{ $fila['cliente'] }}</td>
                        <td><span class="insignia" style="background: {{ $fondo }}; color: {{ $texto }}">{{ $fila['estado'] }}</span></td>
                        <td class="num">{{ $fila['unidades'] }}</td>
                        <td class="num">Q {{ number_format($fila['total'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total"><td colspan="4">{{ $filas->count() }} pedidos</td><td class="num">{{ $sumaUnidades }}</td><td class="num">Q {{ number_format($sumaTotal, 2) }}</td></tr>
            </table>
            @if ($recortado)<p class="nota">Se muestran los primeros {{ $filas->count() }} pedidos. Para el detalle completo, descargue el Excel.</p>@endif
        @endif
    </div>
</body>
</html>
