@php
    use App\Models\EstadoPedido;

    $nombres = [
        EstadoPedido::NUEVO => ['Nuevos', 'Sin pedidos nuevos'],
        EstadoPedido::EN_PROCESO => ['En proceso', 'Nada en preparación'],
        EstadoPedido::LISTO => ['Listos para entregar', 'Nada listo por entregar'],
    ];
    $listos = $columnas->firstWhere('estado.id_estado', EstadoPedido::LISTO)['total'];
    // En el teléfono se abre la primera pestaña que tenga pedidos.
    $pestanaInicial = $columnas->first(fn ($c) => $c['total'] > 0)['estado']->id_estado ?? EstadoPedido::NUEVO;
    $enlaceVentas = route('reportes', ['desde' => $ventas['desde']->toDateString(), 'hasta' => today()->toDateString()]);
@endphp
<x-layouts.app titulo="Hoy" :subtitulo="ucfirst(today()->translatedFormat('l j \d\e F'))">

    @if (! $tieneProductos && $porAtender === 0)
        {{-- Negocio recién creado: lo primero es el catálogo. --}}
        <section class="panel max-w-xl p-6">
            <h2 class="titulo-seccion">Empiece agregando lo que vende</h2>
            <p class="mt-1 text-texto-2">Con sus productos registrados podrá crear pedidos en segundos y verlos aquí, ordenados por etapa.</p>
            <a href="{{ route('productos.create') }}" class="btn btn-primario mt-5">Agregar producto</a>
        </section>
    @else
        {{-- Resumen en una frase: lo pendiente primero, las ventas como contexto. --}}
        <p class="mb-6 text-texto-2">
            @if ($porAtender === 0)
                <span class="text-texto">No tiene pedidos pendientes.</span>
            @else
                <span class="font-medium text-texto">{{ $porAtender }} {{ $porAtender === 1 ? 'pedido' : 'pedidos' }} por atender</span>
                @if ($listos)
                    · {{ $listos }} {{ $listos === 1 ? 'listo' : 'listos' }} para entregar
                @endif
            @endif
            ·
            @if ($ventas['entregas'])
                En los últimos 7 días vendió
                <a href="{{ $enlaceVentas }}" class="enlace tabular-nums">Q {{ number_format($ventas['monto'], 2) }}</a>
                en {{ $ventas['entregas'] }} {{ $ventas['entregas'] === 1 ? 'entrega' : 'entregas' }}.
            @else
                Sin ventas en los últimos 7 días.
            @endif
        </p>

        @if ($porAtender === 0)
            <section class="panel max-w-xl p-6">
                <h2 class="titulo-seccion">Todo al día</h2>
                <p class="mt-1 text-texto-2">Cuando registre un pedido aparecerá aquí, listo para avanzarlo.</p>
                <a href="{{ route('pedidos.create') }}" class="btn btn-primario mt-5"><x-icono nombre="mas" clase="size-4" /> Crear pedido</a>
            </section>
        @else
            {{-- Teléfono: una etapa a la vez, con su número --}}
            <div class="mb-4 flex gap-1 overflow-x-auto rounded-lg bg-superficie-2 p-1 lg:hidden" data-pestanas aria-label="Etapas">
                @foreach ($columnas as $columna)
                    @php $id = $columna['estado']->id_estado; @endphp
                    <button type="button" data-pestana="{{ $id }}" aria-controls="etapa-{{ $id }}" aria-pressed="{{ $id === $pestanaInicial ? 'true' : 'false' }}"
                            class="flex min-h-10 flex-1 cursor-pointer items-center justify-center gap-1.5 rounded-md px-3 text-sm whitespace-nowrap text-texto-2 aria-pressed:bg-superficie aria-pressed:font-medium aria-pressed:text-texto aria-pressed:shadow-[0_1px_2px_rgb(32_32_30/0.08)]">
                        {{ $id === EstadoPedido::LISTO ? 'Listos' : $nombres[$id][0] }}
                        <span class="tabular-nums">{{ $columna['total'] }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Escritorio: las tres etapas lado a lado --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                @foreach ($columnas as $columna)
                    @php $id = $columna['estado']->id_estado; @endphp
                    <section id="etapa-{{ $id }}" data-etapa="{{ $id }}" aria-labelledby="titulo-etapa-{{ $id }}"
                             @class(['flex-col', 'flex' => $id === $pestanaInicial, 'hidden lg:flex' => $id !== $pestanaInicial])>
                        <header class="flex items-center justify-between lg:mb-3">
                            <h2 id="titulo-etapa-{{ $id }}" class="sr-only text-sm font-semibold lg:not-sr-only">{{ $nombres[$id][0] }}</h2>
                            <span class="meta hidden tabular-nums lg:inline">{{ $columna['total'] }}</span>
                        </header>

                        <ul class="flex flex-col gap-3">
                            @forelse ($columna['pedidos'] as $pedido)
                                <li class="panel overflow-hidden transition-colors hover:border-stone-300">
                                    <a href="{{ route('pedidos.show', $pedido) }}" class="block px-4 pt-3.5 pb-3">
                                        <span class="flex items-baseline justify-between gap-3">
                                            <span class="truncate font-medium">{{ $pedido->cliente->nombre }}</span>
                                            <x-moneda :valor="$pedido->total" class="text-sm" />
                                        </span>
                                        <span class="meta mt-0.5 block">
                                            #{{ $pedido->numero() }} · {{ $pedido->fecha->diffForHumans() }} · {{ (int) $pedido->unidades }} {{ (int) $pedido->unidades === 1 ? 'producto' : 'productos' }}
                                        </span>
                                    </a>
                                    <form method="POST" action="{{ route('pedidos.estado', $pedido) }}" class="border-t border-borde" data-envio-unico>
                                        @csrf
                                        <button type="submit" name="id_estado" value="{{ $columna['siguiente']->id_estado }}" data-texto-envio="Actualizando…"
                                                class="flex min-h-11 w-full cursor-pointer items-center justify-between px-4 text-sm font-medium text-marca transition-colors hover:bg-seleccion disabled:cursor-wait disabled:opacity-60 sm:min-h-10">
                                            {{ $columna['siguiente']->accion() }}
                                            <x-icono nombre="flecha-derecha" clase="size-4" />
                                        </button>
                                    </form>
                                </li>
                            @empty
                                <li class="rounded-2xl border border-dashed border-borde px-4 py-8 text-center text-sm text-texto-2">{{ $nombres[$id][1] }}</li>
                            @endforelse
                        </ul>

                        @if ($columna['total'] > $columna['pedidos']->count())
                            <a href="{{ route('pedidos.index', ['estado' => $id]) }}" class="enlace mt-3 text-sm">
                                Ver los {{ $columna['total'] }} pedidos
                            </a>
                        @endif
                    </section>
                @endforeach
            </div>
        @endif
    @endif
</x-layouts.app>
