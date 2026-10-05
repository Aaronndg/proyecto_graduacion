@php
    use App\Models\EstadoPedido;

    $pestanas = [
        'activos' => 'Activos',
        EstadoPedido::ENTREGADO => 'Entregados',
        EstadoPedido::CANCELADO => 'Cancelados',
        'todos' => 'Todos',
    ];
    // Un estado concreto (p. ej. desde «Ver los N» de Hoy) no tiene pestaña propia: se muestra como filtro aparte.
    $estadoSuelto = is_numeric($vista) && ! array_key_exists((int) $vista, $pestanas) ? $estados[(int) $vista] : null;
    $conFechas = $filtros['desde'] || $filtros['hasta'];
    $consulta = fn (array $cambios) => route('pedidos.index', array_filter(array_merge($filtros, ['estado' => $vista], $cambios), fn ($v) => $v !== null && $v !== ''));
    $vacioPestana = [
        'activos' => 'No hay pedidos pendientes.',
        EstadoPedido::ENTREGADO => 'Todavía no hay pedidos entregados.',
        EstadoPedido::CANCELADO => 'No hay pedidos cancelados.',
        'todos' => 'Aún no hay pedidos registrados.',
    ];
@endphp
<x-layouts.app titulo="Pedidos">

    {{-- Pestañas por estado: el filtro principal --}}
    <nav class="-mx-4 mb-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Pedidos por estado" data-desplazable>
        <ul class="flex w-max gap-2 pb-1 sm:w-auto sm:flex-wrap">
            @foreach ($pestanas as $clave => $texto)
                @php $actual = (string) $vista === (string) $clave; @endphp
                <li>
                    <a href="{{ $consulta(['estado' => $clave === 'activos' ? null : $clave]) }}" @if ($actual) aria-current="page" @endif
                       @class([
                           'flex min-h-10 items-center gap-2 rounded-xl px-4 text-sm font-extrabold whitespace-nowrap relieve transition active:translate-y-px',
                           'bg-marca text-white' => $actual,
                           'bg-superficie text-texto-2 hover:text-marca' => ! $actual,
                       ])>
                        {{ $texto }}
                        <span @class(['rounded-md px-1.5 text-[13px] tabular-nums', 'bg-white/20 text-white' => $actual, 'bg-superficie-2' => ! $actual])>{{ $conteos[$clave] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    {{-- Búsqueda y fechas --}}
    <form method="GET" class="mb-4 space-y-3" role="search">
        @if ($vista !== 'activos')<input type="hidden" name="estado" value="{{ $vista }}">@endif
        <div class="flex gap-2">
            <x-campo-busqueda :valor="$filtros['buscar']" placeholder="Número de pedido o cliente" />
            <button type="submit" class="btn btn-secundario">Buscar</button>
        </div>

        <details class="group" @if ($conFechas) open @endif>
            <summary class="inline-flex min-h-9 cursor-pointer list-none items-center gap-1.5 rounded-lg text-sm text-texto-2 hover:text-texto [&::-webkit-details-marker]:hidden">
                <x-icono nombre="flecha-derecha" clase="size-4 transition-transform group-open:rotate-90" />
                Filtrar por fechas
                @if ($conFechas)<span class="font-medium text-texto">(aplicado)</span>@endif
            </summary>
            <div class="mt-2 flex flex-wrap items-end gap-3">
                <div>
                    <label for="desde" class="etiqueta">Desde</label>
                    <input id="desde" name="desde" type="date" value="{{ $filtros['desde'] }}" class="campo w-44">
                </div>
                <div>
                    <label for="hasta" class="etiqueta">Hasta</label>
                    <input id="hasta" name="hasta" type="date" value="{{ $filtros['hasta'] }}" class="campo w-44">
                </div>
                <button type="submit" class="btn btn-secundario">Aplicar fechas</button>
                @if ($conFechas)
                    <a href="{{ $consulta(['desde' => null, 'hasta' => null]) }}" class="btn btn-terciario">Quitar fechas</a>
                @endif
            </div>
        </details>
    </form>

    @if ($estadoSuelto)
        <p class="mb-4 flex flex-wrap items-center gap-2 text-sm text-texto-2">
            Mostrando solo <x-estado-pedido :estado="$estadoSuelto" />
            <a href="{{ $consulta(['estado' => null]) }}" class="enlace">Ver todos los activos</a>
        </p>
    @endif

    @if ($pedidos->isEmpty())
        <div class="panel flex flex-col items-center px-5 py-10 text-center">
            <x-ilustracion nombre="libreta" class="mb-3" />
            @if ($filtros['buscar'])
                <p class="font-medium">No encontramos pedidos con «{{ $filtros['buscar'] }}»</p>
                <p class="mt-1 text-sm text-texto-2">Revise el número o el nombre del cliente.</p>
                <a href="{{ $consulta(['buscar' => null]) }}" class="btn btn-secundario mt-5">Quitar búsqueda</a>
            @elseif ($conteos['todos'] === 0 && ! $conFechas)
                <p class="font-medium">Aún no hay pedidos registrados</p>
                <p class="mt-1 text-sm text-texto-2">Cuando registre uno, aparecerá aquí.</p>
                <a href="{{ route('pedidos.create') }}" class="btn btn-primario mt-5"><x-icono nombre="mas" clase="size-4" /> Crear pedido</a>
            @else
                <p class="font-medium">{{ $estadoSuelto ? 'No hay pedidos en este estado.' : $vacioPestana[$vista] }}</p>
                @if ($conFechas)<p class="mt-1 text-sm text-texto-2">Pruebe con otras fechas.</p>@endif
            @endif
        </div>
    @else
        <div class="panel overflow-hidden">
            {{-- Encabezados (solo escritorio) --}}
            <div class="hidden grid-cols-[4.5rem_minmax(0,1fr)_7rem_8.5rem_7.5rem] gap-4 border-b border-borde bg-superficie-2 px-4 py-2.5 text-[13px] font-medium text-texto-2 md:grid" aria-hidden="true">
                <span>Pedido</span><span>Cliente</span><span>Fecha</span><span>Estado</span><span class="text-right">Total</span>
            </div>

            <ul>
                @foreach ($pedidos as $pedido)
                    @php $productos = (int) $pedido->unidades.' '.((int) $pedido->unidades === 1 ? 'producto' : 'productos'); @endphp
                    <li class="border-b border-borde last:border-b-0">
                        {{-- Toda la fila abre el pedido. Teléfono: cliente y total arriba; número, fecha y estado abajo. --}}
                        <a href="{{ route('pedidos.show', $pedido) }}"
                           class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-1 px-4 py-3 transition-colors hover:bg-superficie-2/60 md:grid-cols-[4.5rem_minmax(0,1fr)_7rem_8.5rem_7.5rem]">
                            <span class="hidden font-medium text-marca tabular-nums md:block">#{{ $pedido->numero() }}</span>
                            <span class="col-start-1 row-start-1 flex min-w-0 items-center gap-3 md:col-start-2">
                                <x-avatar :nombre="$pedido->cliente->nombre" tamano="sm" class="hidden md:inline-flex" />
                                <span class="min-w-0">
                                    <span class="block truncate font-medium">{{ $pedido->cliente->nombre }}</span>
                                    <span class="meta hidden md:block">{{ $productos }}</span>
                                    <x-entrega :pedido="$pedido" class="mt-1" />
                                </span>
                            </span>
                            <span class="meta col-start-1 row-start-2 md:hidden">#{{ $pedido->numero() }} · {{ $pedido->fecha->format('d/m/Y') }}</span>
                            <span class="hidden text-sm text-texto-2 tabular-nums md:col-start-3 md:row-start-1 md:block">{{ $pedido->fecha->format('d/m/Y') }}</span>
                            <span class="col-start-2 row-start-2 justify-self-end md:col-start-4 md:row-start-1 md:justify-self-start"><x-estado-pedido :estado="$pedido->estado" /></span>
                            <x-moneda :valor="$pedido->total" class="col-start-2 row-start-1 text-right font-medium md:col-start-5" />
                        </a>
                    </li>
                @endforeach
            </ul>

            @if ($pedidos->hasPages())<div class="border-t border-borde px-4 py-3">{{ $pedidos->links() }}</div>@endif
        </div>
    @endif
</x-layouts.app>
