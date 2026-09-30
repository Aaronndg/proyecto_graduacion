@php
    $hoy = today();
    $rapidos = [
        'Últimos 7 días' => [$hoy->copy()->subDays(6), $hoy],
        'Últimos 30 días' => [$hoy->copy()->subDays(29), $hoy],
        'Este mes' => [$hoy->copy()->startOfMonth(), $hoy],
        'Mes anterior' => [$hoy->copy()->subMonthNoOverflow()->startOfMonth(), $hoy->copy()->subMonthNoOverflow()->endOfMonth()],
    ];
    $periodo = \Illuminate\Support\Carbon::parse($filtros['desde'])->format('d/m/Y').' al '.\Illuminate\Support\Carbon::parse($filtros['hasta'])->format('d/m/Y');
    $maxVenta = max($ventas->max('monto'), 0.01);
    $maxEstado = max($porEstado->max('cantidad'), 1);
    $cadaEtiqueta = max(1, (int) ceil($ventas->count() / 6));
@endphp
<x-layouts.app titulo="Reportes" :subtitulo="'Pedidos y ventas del '.$periodo.'.'">
    <x-slot:acciones>
        <a href="{{ route('reportes.exportar', request()->query()) }}" class="btn btn-secundario print:hidden">
            <x-icono nombre="descargar" clase="size-4" /> Descargar Excel (CSV)
        </a>
        <button type="button" data-imprimir class="btn btn-secundario print:hidden">
            <x-icono nombre="imprimir" clase="size-4" /> Imprimir
        </button>
    </x-slot:acciones>

    @if ($errors->any())
        <div role="alert" class="mb-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm font-medium text-red-800">
            <x-icono nombre="alerta" clase="size-5 shrink-0 text-red-600" />
            <span>{{ $errors->first() }} Se muestran los últimos 30 días.</span>
        </div>
    @endif

    {{-- Filtros de consulta --}}
    <form method="GET" class="tarjeta mb-6 p-4 print:hidden" aria-label="Filtros del reporte">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto] lg:items-end">
            <div>
                <label for="desde" class="etiqueta">Desde</label>
                <input id="desde" name="desde" type="date" value="{{ $filtros['desde'] }}" max="{{ $hoy->toDateString() }}" class="campo" required>
            </div>
            <div>
                <label for="hasta" class="etiqueta">Hasta</label>
                <input id="hasta" name="hasta" type="date" value="{{ $filtros['hasta'] }}" class="campo" required>
            </div>
            <div>
                <label for="estado" class="etiqueta">Estado (detalle)</label>
                <select id="estado" name="estado" class="campo">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->id_estado }}" @selected($filtros['estado'] === $estado->id_estado)>{{ $estado->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primario"><x-icono nombre="buscar" clase="size-4" /> Consultar</button>
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach ($rapidos as $texto => [$desde, $hasta])
                @php $activo = $filtros['desde'] === $desde->toDateString() && $filtros['hasta'] === $hasta->toDateString(); @endphp
                <a href="{{ route('reportes', array_filter(['desde' => $desde->toDateString(), 'hasta' => $hasta->toDateString(), 'estado' => $filtros['estado']])) }}"
                   @if ($activo) aria-current="true" @endif
                   @class(['rounded-full px-3 py-1 text-xs font-medium ring-1 ring-inset transition',
                           'bg-marca-50 text-marca-800 ring-marca-600/30' => $activo,
                           'text-stone-600 ring-stone-300 hover:bg-stone-100' => ! $activo])>{{ $texto }}</a>
            @endforeach
        </div>
    </form>

    <p class="mb-4 hidden text-sm text-stone-600 print:block">Período: {{ $periodo }} · Generado el {{ now()->format('d/m/Y H:i') }}</p>

    {{-- Resumen --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-estadistica titulo="Pedidos del período" :valor="$resumen['pedidos']" icono="pedido" color="bg-sky-50 text-sky-600" />
        <x-estadistica titulo="Ventas (entregados)" :valor="$resumen['ventas']" icono="ok" color="bg-emerald-50 text-emerald-600" />
        <x-estadistica titulo="En curso" :valor="$resumen['en_curso']" icono="reloj" color="bg-amber-50 text-amber-600" />
        <x-estadistica titulo="Monto vendido" :valor="'Q '.number_format($resumen['monto_vendido'], 2)" icono="reportes" />
    </div>
    <p class="mt-2 text-xs text-stone-500">
        Venta promedio: <x-moneda :valor="$resumen['ticket_promedio']" class="font-medium text-stone-700" />
        · Cancelados: <span class="font-medium text-stone-700">{{ $resumen['cancelados'] }}</span>
        · Solo los pedidos entregados cuentan como venta.
    </p>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[2fr_1fr]">
        {{-- Ventas por período (una sola serie: monto vendido) --}}
        <section class="tarjeta p-5 break-inside-avoid" aria-labelledby="titulo-ventas">
            <h2 id="titulo-ventas" class="font-semibold text-stone-900">Ventas por {{ $porMes ? 'mes' : 'día' }}</h2>
            <p class="text-sm text-stone-500">Monto de los pedidos entregados. Pase el cursor o toque una barra para ver el detalle.</p>

            @if ($resumen['ventas'] === 0)
                <x-vacio icono="reportes" titulo="Sin ventas en este período" texto="Cuando marque pedidos como entregados, aparecerán aquí." />
            @else
                <div class="mt-5 flex gap-2">
                    {{-- Eje: solo el máximo y el cero, en tinta tenue --}}
                    <div class="flex h-48 flex-col justify-between text-right text-[11px] text-stone-400 tabular-nums" aria-hidden="true">
                        <span>Q {{ number_format($maxVenta, 0) }}</span><span>Q 0</span>
                    </div>
                    <div class="relative flex h-48 min-w-0 flex-1 items-end gap-[2px] border-b border-stone-200" role="list" aria-label="Ventas por período">
                        <div class="pointer-events-none absolute inset-x-0 top-0 border-t border-dashed border-stone-100"></div>
                        @foreach ($ventas as $punto)
                            <div class="group relative flex h-full flex-1 items-end justify-center focus:outline-none" role="listitem" tabindex="0"
                                 aria-label="{{ $punto['detalle'] }}: Q {{ number_format($punto['monto'], 2) }}, {{ $punto['ventas'] }} venta(s)">
                                <div @class(['w-full max-w-8 rounded-t-[4px] transition-colors',
                                             'bg-marca-500 group-hover:bg-marca-700 group-focus:bg-marca-700' => $punto['monto'] > 0])
                                     style="height: {{ $punto['monto'] > 0 ? max(2, round($punto['monto'] / $maxVenta * 100, 2)) : 0 }}%"></div>
                                <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 hidden -translate-x-1/2 rounded-lg bg-stone-900 px-2.5 py-1.5 text-xs whitespace-nowrap text-white shadow-lg group-hover:block group-focus:block">
                                    <span class="block text-stone-300">{{ $punto['detalle'] }}</span>
                                    <span class="font-semibold tabular-nums">Q {{ number_format($punto['monto'], 2) }}</span>
                                    <span class="text-stone-300">· {{ $punto['ventas'] }} venta(s)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                {{-- Etiquetas posicionadas sobre su barra, sin ocupar ancho (no desbordan en móvil) --}}
                <div class="relative mt-1.5 ml-12 h-4 text-[11px] text-stone-400" aria-hidden="true">
                    @foreach ($ventas as $i => $punto)
                        @if ($i % $cadaEtiqueta === 0)
                            <span class="absolute -translate-x-1/2 whitespace-nowrap" style="left: {{ round(($i + 0.5) / $ventas->count() * 100, 3) }}%">{{ $punto['etiqueta'] }}</span>
                        @endif
                    @endforeach
                </div>

                <details class="mt-4 text-sm print:hidden">
                    <summary class="enlace cursor-pointer">Ver como tabla</summary>
                    <div class="mt-2 max-h-64 overflow-y-auto">
                        <table class="tabla">
                            <thead><tr><th>Período</th><th class="text-right">Ventas</th><th class="text-right">Monto</th></tr></thead>
                            <tbody class="divide-y divide-stone-100">
                                @foreach ($ventas->where('ventas', '>', 0) as $punto)
                                    <tr><td>{{ $punto['detalle'] }}</td><td class="text-right tabular-nums">{{ $punto['ventas'] }}</td><td class="text-right"><x-moneda :valor="$punto['monto']" /></td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif
        </section>

        {{-- Pedidos por estado: la etiqueta de texto identifica cada barra --}}
        <section class="tarjeta p-5 break-inside-avoid" aria-labelledby="titulo-estados">
            <h2 id="titulo-estados" class="font-semibold text-stone-900">Pedidos por estado</h2>
            <p class="text-sm text-stone-500">Todos los pedidos del período.</p>
            <ul class="mt-5 space-y-4">
                @foreach ($porEstado as $fila)
                    <li>
                        <div class="mb-1.5 flex items-center justify-between text-sm">
                            <span class="font-medium text-stone-700">{{ $fila['estado']->nombre }}</span>
                            <span class="tabular-nums text-stone-600">
                                {{ $fila['cantidad'] }}
                                @if ($resumen['pedidos'])<span class="text-stone-400">({{ round($fila['cantidad'] / $resumen['pedidos'] * 100) }} %)</span>@endif
                            </span>
                        </div>
                        <div class="h-2.5 rounded-full bg-stone-100">
                            <div class="h-full rounded-full {{ $fila['estado']->colorBarra() }}" style="width: {{ round($fila['cantidad'] / $maxEstado * 100, 2) }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    {{-- Productos más vendidos (RF-14) --}}
    <section class="tarjeta mt-6 overflow-hidden break-inside-avoid" aria-labelledby="titulo-productos">
        <div class="px-5 pt-5 pb-3">
            <h2 id="titulo-productos" class="font-semibold text-stone-900">Productos más vendidos</h2>
            <p class="text-sm text-stone-500">Según los pedidos entregados del período.</p>
        </div>
        @if ($productos->isEmpty())
            <p class="px-5 pb-5 text-sm text-stone-500">Aún no hay productos vendidos en este período.</p>
        @else
            <div class="overflow-x-auto">
                <table class="tabla">
                    <thead><tr><th>Producto</th><th class="text-right">Unidades</th><th class="text-right">Monto</th><th class="hidden sm:table-cell">Participación</th></tr></thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($productos as $producto)
                            @php $parte = $resumen['monto_vendido'] > 0 ? $producto['monto'] / $resumen['monto_vendido'] * 100 : 0; @endphp
                            <tr>
                                <td class="font-medium text-stone-900">{{ $producto['nombre'] }}</td>
                                <td class="text-right tabular-nums">{{ $producto['unidades'] }}</td>
                                <td class="text-right font-medium"><x-moneda :valor="$producto['monto']" /></td>
                                <td class="hidden w-1/3 sm:table-cell">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 flex-1 rounded-full bg-stone-100"><div class="h-full rounded-full bg-marca-500" style="width: {{ round($parte, 2) }}%"></div></div>
                                        <span class="w-10 text-right text-xs text-stone-500 tabular-nums">{{ round($parte) }} %</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Detalle de pedidos --}}
    <section class="tarjeta mt-6 overflow-hidden" aria-labelledby="titulo-detalle">
        <div class="px-5 pt-5 pb-3">
            <h2 id="titulo-detalle" class="font-semibold text-stone-900">Detalle de pedidos</h2>
            <p class="text-sm text-stone-500">
                {{ $pedidos->total() }} pedido(s){{ $filtros['estado'] ? ' en estado «'.$estados->firstWhere('id_estado', $filtros['estado'])?->nombre.'»' : '' }}.
            </p>
        </div>
        @if ($pedidos->isEmpty())
            <x-vacio titulo="No hay pedidos en este período" texto="Pruebe con otro rango de fechas o estado." />
        @else
            <div class="overflow-x-auto">
                <table class="tabla">
                    <thead><tr><th>Pedido</th><th>Cliente</th><th>Fecha</th><th>Estado</th><th class="text-right">Productos</th><th class="text-right">Total</th></tr></thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($pedidos as $pedido)
                            <tr>
                                <td><a href="{{ route('pedidos.show', $pedido) }}" class="enlace">#{{ $pedido->numero() }}</a></td>
                                <td class="font-medium text-stone-900">{{ $pedido->cliente->nombre }}</td>
                                <td class="whitespace-nowrap text-stone-500">{{ $pedido->fecha->format('d/m/Y') }}</td>
                                <td><x-estado-pedido :estado="$pedido->estado" /></td>
                                <td class="text-right tabular-nums">{{ (int) $pedido->unidades }}</td>
                                <td class="text-right font-medium"><x-moneda :valor="$pedido->total" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($pedidos->hasPages())<div class="border-t border-stone-100 px-5 py-3 print:hidden">{{ $pedidos->links() }}</div>@endif
        @endif
    </section>
</x-layouts.app>
