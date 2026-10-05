@php
    use Illuminate\Support\Carbon;

    $hoy = today();
    $rapidos = [
        'Últimos 7 días' => [$hoy->copy()->subDays(6), $hoy],
        'Últimos 30 días' => [$hoy->copy()->subDays(29), $hoy],
        'Este mes' => [$hoy->copy()->startOfMonth(), $hoy],
        'Mes anterior' => [$hoy->copy()->subMonthNoOverflow()->startOfMonth(), $hoy->copy()->subMonthNoOverflow()->endOfMonth()],
    ];
    $desde = Carbon::parse($filtros['desde']);
    $hasta = Carbon::parse($filtros['hasta']);
    $periodo = 'Del '.$desde->translatedFormat($desde->year === $hasta->year ? 'j \d\e F' : 'j \d\e F \d\e Y').' al '.$hasta->translatedFormat('j \d\e F \d\e Y');
    $atajoActivo = collect($rapidos)->search(fn ($r) => $filtros['desde'] === $r[0]->toDateString() && $filtros['hasta'] === $r[1]->toDateString());
    $maxVenta = max($ventas->max('monto'), 0.01);
    $maxEstado = max($porEstado->max('cantidad'), 1);
    $cadaEtiqueta = max(1, (int) ceil($ventas->count() / 6));
    $plural = fn (int $n, string $uno, string $varios) => $n.' '.($n === 1 ? $uno : $varios);
    $estadoDetalle = $filtros['estado'] ? $estados->firstWhere('id_estado', $filtros['estado']) : null;
@endphp
<x-layouts.app titulo="Reportes">
    <x-slot:acciones>
        <div class="print:hidden">
            <x-menu-acciones etiqueta="Descargar o imprimir el reporte">
                <a href="{{ route('reportes.exportar', request()->query()) }}" class="menu-opcion"><x-icono nombre="descargar" clase="size-4 text-texto-2" /> Descargar Excel (CSV)</a>
                <button type="button" data-imprimir class="menu-opcion"><x-icono nombre="imprimir" clase="size-4 text-texto-2" /> Imprimir</button>
            </x-menu-acciones>
        </div>
    </x-slot:acciones>

    @if ($errors->any())
        <div role="alert" class="mb-6 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <x-icono nombre="alerta" clase="size-5 shrink-0 text-red-600" />
            <span>{{ $errors->first() }} Se muestran los últimos 30 días.</span>
        </div>
    @endif

    {{-- Período: atajos como pestañas; otras fechas en un desplegable --}}
    <div class="mb-6 print:hidden">
        <nav class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Período del reporte" data-desplazable>
            <ul class="flex w-max gap-2 pb-1 sm:w-auto sm:flex-wrap">
                @foreach ($rapidos as $texto => [$inicio, $fin])
                    @php $actual = $atajoActivo === $texto; @endphp
                    <li>
                        <a href="{{ route('reportes', array_filter(['desde' => $inicio->toDateString(), 'hasta' => $fin->toDateString(), 'estado' => $filtros['estado']])) }}"
                           @if ($actual) aria-current="page" @endif
                           @class([
                               'flex min-h-10 items-center rounded-xl px-4 text-sm font-extrabold whitespace-nowrap relieve transition active:translate-y-px',
                               'bg-marca text-white' => $actual,
                               'bg-superficie text-texto-2 hover:text-marca' => ! $actual,
                           ])>{{ $texto }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <details class="group mt-3" @if ($atajoActivo === false || $errors->any()) open @endif>
            <summary class="inline-flex min-h-9 cursor-pointer list-none items-center gap-1.5 text-sm text-texto-2 hover:text-texto [&::-webkit-details-marker]:hidden">
                <x-icono nombre="flecha-derecha" clase="size-4 transition-transform group-open:rotate-90" />
                Otras fechas
            </summary>
            <form method="GET" class="mt-2 flex flex-wrap items-end gap-3" aria-label="Elegir fechas del reporte">
                @if ($filtros['estado'])<input type="hidden" name="estado" value="{{ $filtros['estado'] }}">@endif
                <div>
                    <label for="desde" class="etiqueta">Desde</label>
                    <input id="desde" name="desde" type="date" value="{{ $filtros['desde'] }}" max="{{ $hoy->toDateString() }}" class="campo w-44" required>
                </div>
                <div>
                    <label for="hasta" class="etiqueta">Hasta</label>
                    <input id="hasta" name="hasta" type="date" value="{{ $filtros['hasta'] }}" class="campo w-44" required>
                </div>
                <button type="submit" class="btn btn-secundario">Ver reporte</button>
            </form>
        </details>
    </div>

    {{-- Resumen en una frase: lo importante primero --}}
    <section class="mb-6" aria-labelledby="titulo-resumen">
        <h2 id="titulo-resumen" class="meta mb-1">{{ $periodo }}<span class="hidden print:inline"> · Generado el {{ now()->format('d/m/Y H:i') }}</span></h2>
        @if ($resumen['ventas'])
            <p class="text-xl font-semibold tracking-tight text-balance sm:text-2xl">
                Vendió <x-moneda :valor="$resumen['monto_vendido']" /> en {{ $plural($resumen['ventas'], 'entrega', 'entregas') }}
            </p>
        @else
            <p class="text-xl font-semibold tracking-tight sm:text-2xl">Sin ventas en este período</p>
        @endif

        {{-- Comparación con el período anterior de la misma duración, en palabras --}}
        @php
            $antes = $anterior['monto_vendido'];
            $ahora = $resumen['monto_vendido'];
            $fechasAntes = 'del '.$anterior['desde']->translatedFormat('j M').' al '.$anterior['hasta']->translatedFormat('j M');
            $cambio = $antes > 0 ? round(($ahora - $antes) / $antes * 100) : null;
        @endphp
        @if ($antes > 0 || $ahora > 0)
            <p class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                @if ($cambio === null)
                    <span class="insignia bg-emerald-50 text-emerald-700">Nuevo</span>
                    <span class="text-texto-2">En el período anterior ({{ $fechasAntes }}) no tuvo ventas.</span>
                @elseif ($cambio == 0)
                    <span class="insignia bg-superficie-2 text-texto-2">= Igual</span>
                    <span class="text-texto-2">Vendió lo mismo que en el período anterior ({{ $fechasAntes }}).</span>
                @else
                    <span @class(['insignia', 'bg-emerald-50 text-emerald-700' => $cambio > 0, 'bg-red-50 text-red-700' => $cambio < 0])>
                        {{ $cambio > 0 ? '▲' : '▼' }} {{ abs($cambio) }} %
                    </span>
                    <span class="text-texto-2">
                        {{ $cambio > 0 ? 'Más' : 'Menos' }} que en el período anterior ({{ $fechasAntes }}), cuando vendió
                        <span class="font-bold text-texto tabular-nums">Q {{ number_format($antes, 2) }}</span>.
                    </span>
                @endif
            </p>
        @endif
        <p class="mt-1 text-sm text-texto-2">
            {{ collect([
                $resumen['ventas'] ? 'Venta promedio Q '.number_format($resumen['ticket_promedio'], 2) : null,
                $plural($resumen['pedidos'], 'pedido', 'pedidos').' en el período',
                $resumen['en_curso'].' en curso',
                $plural($resumen['cancelados'], 'cancelado', 'cancelados'),
            ])->filter()->implode(' · ') }}.
            Solo los pedidos entregados cuentan como venta.
        </p>
    </section>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        {{-- Ventas por período (una sola serie: monto vendido) --}}
        <section class="panel p-5 break-inside-avoid" aria-labelledby="titulo-ventas">
            <h2 id="titulo-ventas" class="titulo-seccion">Ventas por {{ $porMes ? 'mes' : 'día' }}</h2>
            <p class="meta">Monto de los pedidos entregados. Toque una barra para ver el detalle.</p>

            @if ($resumen['ventas'] === 0)
                <p class="mt-6 rounded-lg bg-superficie-2 px-4 py-6 text-center text-sm text-texto-2">Cuando marque pedidos como entregados, aparecerán aquí.</p>
            @else
                <div class="mt-5 flex gap-2">
                    <div class="flex h-48 flex-col justify-between text-right text-[11px] text-texto-2 tabular-nums" aria-hidden="true">
                        <span>Q {{ number_format($maxVenta, 0) }}</span><span>Q 0</span>
                    </div>
                    <div class="relative flex h-48 min-w-0 flex-1 items-end gap-[2px] border-b border-borde" role="list" aria-label="Ventas por período">
                        <div class="pointer-events-none absolute inset-x-0 top-0 border-t border-dashed border-borde"></div>
                        @foreach ($ventas as $punto)
                            <div class="group relative flex h-full flex-1 items-end justify-center focus:outline-none" role="listitem" tabindex="0"
                                 aria-label="{{ $punto['detalle'] }}: Q {{ number_format($punto['monto'], 2) }}, {{ $punto['ventas'] }} venta(s)">
                                <div @class(['w-full max-w-8 rounded-t-[3px] transition-colors',
                                             'bg-marca/80 group-hover:bg-marca group-focus:bg-marca' => $punto['monto'] > 0])
                                     style="height: {{ $punto['monto'] > 0 ? max(2, round($punto['monto'] / $maxVenta * 100, 2)) : 0 }}%"></div>
                                <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 hidden -translate-x-1/2 rounded-lg bg-stone-900 px-2.5 py-1.5 text-xs whitespace-nowrap text-white shadow-flotante group-hover:block group-focus:block">
                                    <span class="block text-stone-300">{{ $punto['detalle'] }}</span>
                                    <span class="font-semibold tabular-nums">Q {{ number_format($punto['monto'], 2) }}</span>
                                    <span class="text-stone-300">· {{ $punto['ventas'] }} venta(s)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="relative mt-1.5 ml-12 h-4 text-[11px] text-texto-2" aria-hidden="true">
                    @foreach ($ventas as $i => $punto)
                        @if ($i % $cadaEtiqueta === 0)
                            <span class="absolute -translate-x-1/2 whitespace-nowrap" style="left: {{ round(($i + 0.5) / $ventas->count() * 100, 3) }}%">{{ $punto['etiqueta'] }}</span>
                        @endif
                    @endforeach
                </div>

                <details class="group mt-4 text-sm print:hidden">
                    <summary class="inline-flex min-h-9 cursor-pointer list-none items-center gap-1.5 text-texto-2 hover:text-texto [&::-webkit-details-marker]:hidden">
                        <x-icono nombre="flecha-derecha" clase="size-4 transition-transform group-open:rotate-90" />
                        Ver como tabla
                    </summary>
                    <ul class="mt-2 max-h-64 overflow-y-auto rounded-lg border border-borde">
                        @foreach ($ventas->where('ventas', '>', 0) as $punto)
                            <li class="flex items-baseline justify-between gap-4 border-b border-borde px-3 py-2 last:border-b-0">
                                <span>{{ $punto['detalle'] }} <span class="meta">· {{ $plural($punto['ventas'], 'venta', 'ventas') }}</span></span>
                                <x-moneda :valor="$punto['monto']" />
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </section>

        {{-- Pedidos por estado: la etiqueta de texto identifica cada barra --}}
        <section class="panel p-5 break-inside-avoid" aria-labelledby="titulo-estados">
            <h2 id="titulo-estados" class="titulo-seccion">Pedidos por estado</h2>
            <p class="meta">Todos los pedidos del período.</p>
            <ul class="mt-5 space-y-4">
                @foreach ($porEstado as $fila)
                    <li>
                        <div class="mb-1.5 flex items-center justify-between text-sm">
                            <span>{{ $fila['estado']->nombre }}</span>
                            <span class="tabular-nums">
                                {{ $fila['cantidad'] }}
                                @if ($resumen['pedidos'])<span class="text-texto-2">· {{ round($fila['cantidad'] / $resumen['pedidos'] * 100) }} %</span>@endif
                            </span>
                        </div>
                        <div class="h-2 rounded-full bg-superficie-2">
                            <x-estado-pedido :estado="$fila['estado']" barra style="width: {{ round($fila['cantidad'] / $maxEstado * 100, 2) }}%" />
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    {{-- Productos más vendidos (RF-14) --}}
    <section class="panel mt-6 overflow-hidden break-inside-avoid" aria-labelledby="titulo-productos">
        <div class="px-5 pt-4 pb-3">
            <h2 id="titulo-productos" class="titulo-seccion">Productos más vendidos</h2>
            <p class="meta">Según los pedidos entregados del período.</p>
        </div>
        @if ($productos->isEmpty())
            <p class="border-t border-borde px-5 py-4 text-sm text-texto-2">Aún no hay productos vendidos en este período.</p>
        @else
            <ul class="border-t border-borde">
                @foreach ($productos as $producto)
                    @php $parte = $resumen['monto_vendido'] > 0 ? $producto['monto'] / $resumen['monto_vendido'] * 100 : 0; @endphp
                    <li class="border-b border-borde px-5 py-3 last:border-b-0">
                        <div class="flex items-baseline justify-between gap-4">
                            <span class="min-w-0">
                                <span class="font-medium">{{ $producto['nombre'] }}</span>
                                <span class="meta block sm:inline"><span class="hidden sm:inline">· </span>{{ $plural((int) $producto['unidades'], 'unidad', 'unidades') }}</span>
                            </span>
                            <x-moneda :valor="$producto['monto']" class="font-medium" />
                        </div>
                        <div class="mt-2 flex items-center gap-3">
                            <div class="h-1.5 flex-1 rounded-full bg-superficie-2"><div class="h-full rounded-full bg-marca/80" style="width: {{ round($parte, 2) }}%"></div></div>
                            <span class="w-10 text-right text-[13px] text-texto-2 tabular-nums">{{ round($parte) }} %</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Detalle de pedidos; el filtro de estado solo afecta a esta sección --}}
    <section class="panel mt-6 overflow-hidden" aria-labelledby="titulo-detalle">
        <div class="flex flex-wrap items-end justify-between gap-3 px-5 pt-4 pb-3">
            <div>
                <h2 id="titulo-detalle" class="titulo-seccion">Detalle de pedidos</h2>
                <p class="meta">{{ $plural($pedidos->total(), 'pedido', 'pedidos') }}{{ $estadoDetalle ? ' en estado «'.$estadoDetalle->nombre.'»' : '' }}.</p>
            </div>
            <form method="GET" class="flex w-full items-center gap-2 sm:w-auto print:hidden" aria-label="Filtrar el detalle por estado">
                <input type="hidden" name="desde" value="{{ $filtros['desde'] }}">
                <input type="hidden" name="hasta" value="{{ $filtros['hasta'] }}">
                <label for="estado" class="sr-only">Estado</label>
                <select id="estado" name="estado" class="campo min-w-0 flex-1 sm:w-44 sm:flex-none">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->id_estado }}" @selected($filtros['estado'] === $estado->id_estado)>{{ $estado->nombre }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-secundario">Filtrar</button>
            </form>
        </div>

        @if ($pedidos->isEmpty())
            <p class="border-t border-borde px-5 py-6 text-center text-sm text-texto-2">No hay pedidos{{ $estadoDetalle ? ' en este estado' : '' }} en este período.</p>
        @else
            <div class="hidden grid-cols-[4.5rem_minmax(0,1fr)_7rem_8.5rem_5.5rem_7.5rem] gap-4 border-y border-borde bg-superficie-2 px-5 py-2.5 text-[13px] font-medium text-texto-2 md:grid" aria-hidden="true">
                <span>Pedido</span><span>Cliente</span><span>Fecha</span><span>Estado</span><span class="text-right">Productos</span><span class="text-right">Total</span>
            </div>
            <ul class="border-t border-borde md:border-t-0">
                @foreach ($pedidos as $pedido)
                    <li class="border-b border-borde last:border-b-0">
                        <a href="{{ route('pedidos.show', $pedido) }}"
                           class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-1 px-5 py-3 transition-colors hover:bg-superficie-2/60 md:grid-cols-[4.5rem_minmax(0,1fr)_7rem_8.5rem_5.5rem_7.5rem]">
                            <span class="hidden font-medium text-marca tabular-nums md:block">#{{ $pedido->numero() }}</span>
                            <span class="col-start-1 row-start-1 truncate font-medium md:col-start-2">{{ $pedido->cliente->nombre }}</span>
                            <span class="meta col-start-1 row-start-2 md:hidden">#{{ $pedido->numero() }} · {{ $pedido->fecha->format('d/m/Y') }}</span>
                            <span class="hidden text-sm text-texto-2 tabular-nums md:col-start-3 md:row-start-1 md:block">{{ $pedido->fecha->format('d/m/Y') }}</span>
                            <span class="col-start-2 row-start-2 justify-self-end md:col-start-4 md:row-start-1 md:justify-self-start"><x-estado-pedido :estado="$pedido->estado" /></span>
                            <span class="hidden text-right text-sm tabular-nums md:col-start-5 md:row-start-1 md:block">{{ (int) $pedido->unidades }}</span>
                            <x-moneda :valor="$pedido->total" class="col-start-2 row-start-1 text-right font-medium md:col-start-6" />
                        </a>
                    </li>
                @endforeach
            </ul>
            @if ($pedidos->hasPages())<div class="border-t border-borde px-5 py-3 print:hidden">{{ $pedidos->links() }}</div>@endif
        @endif
    </section>
</x-layouts.app>
