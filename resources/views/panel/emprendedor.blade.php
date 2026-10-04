@php
    use App\Models\EstadoPedido;

    // Nombre, texto vacío y color de cada etapa (el mismo de su etiqueta de estado).
    $etapas = [
        EstadoPedido::NUEVO => ['Nuevos', 'Sin pedidos nuevos', 'sky'],
        EstadoPedido::EN_PROCESO => ['En proceso', 'Nada en preparación', 'amber'],
        EstadoPedido::LISTO => ['Listos para entregar', 'Nada listo por entregar', 'emerald'],
    ];
    $tonos = [
        'sky' => ['fondo' => 'bg-sky-50/70', 'punto' => 'bg-sky-500', 'borde' => 'border-t-sky-500', 'boton' => 'bg-sky-50 text-sky-700 hover:bg-sky-100', 'pestana' => 'aria-pressed:bg-sky-500'],
        'amber' => ['fondo' => 'bg-amber-50/70', 'punto' => 'bg-amber-500', 'borde' => 'border-t-amber-500', 'boton' => 'bg-amber-50 text-amber-700 hover:bg-amber-100', 'pestana' => 'aria-pressed:bg-amber-500'],
        'emerald' => ['fondo' => 'bg-emerald-50/70', 'punto' => 'bg-emerald-500', 'borde' => 'border-t-emerald-500', 'boton' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100', 'pestana' => 'aria-pressed:bg-emerald-500'],
    ];
    $listos = $columnas->firstWhere('estado.id_estado', EstadoPedido::LISTO)['total'];
    // En el teléfono se abre la primera pestaña que tenga pedidos.
    $pestanaInicial = $columnas->first(fn ($c) => $c['total'] > 0)['estado']->id_estado ?? EstadoPedido::NUEVO;
    $enlaceVentas = route('reportes', ['desde' => $ventas['desde']->toDateString(), 'hasta' => today()->toDateString()]);
    $hora = now()->hour;
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
    $nuevoNegocio = ! $tieneProductos && $porAtender === 0;
    $accesos = [
        ['ruta' => route('pedidos.create'), 'icono' => 'mas', 'texto' => 'Nuevo pedido', 'corto' => 'Pedido', 'detalle' => 'Registre lo que le pidieron', 'tono' => 'bg-oro-suave'],
        ['ruta' => route('clientes.index'), 'icono' => 'usuarios', 'texto' => 'Clientes', 'corto' => 'Clientes', 'detalle' => 'Quiénes le compran', 'tono' => 'bg-marca-100'],
        ['ruta' => route('productos.index'), 'icono' => 'producto', 'texto' => 'Productos', 'corto' => 'Productos', 'detalle' => 'Lo que vende y su precio', 'tono' => 'bg-oro-suave'],
        ['ruta' => route('reportes'), 'icono' => 'reportes', 'texto' => 'Reportes', 'corto' => 'Reportes', 'detalle' => 'Ventas y lo más vendido', 'tono' => 'bg-marca-100'],
    ];
@endphp
<x-layouts.app titulo="Hoy" :encabezado="false">

    {{-- Banner de bienvenida: el saludo y lo pendiente del día --}}
    <section class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-marca to-[#1D3D63] px-5 py-6 text-white shadow-suave sm:px-8 sm:py-8" aria-labelledby="saludo">
        <span class="pointer-events-none absolute -top-20 -right-16 size-64 rounded-full bg-oro/15" aria-hidden="true"></span>
        <span class="pointer-events-none absolute -bottom-24 right-40 hidden size-48 rounded-full bg-white/5 lg:block" aria-hidden="true"></span>

        <div class="relative grid items-center gap-6 lg:grid-cols-[1fr_auto]">
            <div>
                <p class="text-sm font-bold text-stone-300">{{ ucfirst(today()->translatedFormat('l j \d\e F')) }}</p>
                <h1 id="saludo" class="mt-1 font-display text-[28px] leading-tight font-semibold sm:text-4xl">¡{{ $saludo }}, {{ strtok(auth()->user()->nombre, ' ') }}!</h1>

                @if ($nuevoNegocio)
                    <p class="mt-2 max-w-lg text-[17px] text-stone-200"><b class="text-white">Empiece agregando lo que vende.</b> Con sus productos registrados podrá crear pedidos en segundos y verlos aquí, ordenados por etapa.</p>
                    <div class="mt-5"><a href="{{ route('productos.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Agregar producto</a></div>
                @else
                    <p class="mt-2 text-[17px] text-stone-200">
                        @if ($porAtender === 0)
                            No tiene pedidos pendientes.
                        @else
                            Tiene <b class="text-white">{{ $porAtender }} {{ $porAtender === 1 ? 'pedido' : 'pedidos' }} por atender</b>{{ $listos ? ' y '.$listos.' '.($listos === 1 ? 'listo' : 'listos').' para entregar' : '' }}.
                        @endif
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        <a href="{{ route('pedidos.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Crear pedido</a>
                        <a href="{{ route('pedidos.index') }}" class="btn border-2 border-white/70 text-white hover:bg-white/10">Ver todos los pedidos</a>
                    </div>
                    <p class="mt-4 inline-flex flex-wrap items-center gap-x-1.5 rounded-full bg-white/10 px-3.5 py-1.5 text-sm">
                        @if ($ventas['entregas'])
                            En los últimos 7 días vendió
                            <a href="{{ $enlaceVentas }}" class="font-extrabold text-[#E9C46A] tabular-nums hover:underline">Q {{ number_format($ventas['monto'], 2) }}</a>
                            en {{ $ventas['entregas'] }} {{ $ventas['entregas'] === 1 ? 'entrega' : 'entregas' }}.
                        @else
                            Sin ventas en los últimos 7 días.
                        @endif
                    </p>
                @endif
            </div>

            {{-- Ilustración: cajas de pedido y la marca de entregado --}}
            <svg class="hidden h-40 w-56 lg:block" viewBox="0 0 210 150" aria-hidden="true">
                <rect x="30" y="62" width="78" height="64" rx="10" fill="#F6EBD2" />
                <rect x="30" y="62" width="78" height="16" rx="8" fill="#E8CD8F" />
                <rect x="62" y="62" width="14" height="64" fill="#C18D21" opacity=".55" />
                <rect x="112" y="38" width="70" height="88" rx="10" fill="#CFCED0" />
                <rect x="112" y="38" width="70" height="14" rx="7" fill="#B3B3B3" />
                <rect x="140" y="38" width="13" height="88" fill="#0C1F34" opacity=".25" />
                <circle cx="170" cy="34" r="20" fill="#C18D21" />
                <path d="m161 34 6 6 12-12" stroke="#0A101A" stroke-width="4.5" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                <rect x="12" y="126" width="186" height="5" rx="2.5" fill="#FFFFFF" opacity=".35" />
            </svg>
        </div>
    </section>

    {{-- Accesos: íconos en círculos --}}
    <nav class="mb-8 grid grid-cols-4 gap-2 sm:gap-4" aria-label="Accesos rápidos">
        @foreach ($accesos as $acceso)
            <a href="{{ $acceso['ruta'] }}" class="group flex flex-col items-center gap-2 rounded-2xl p-1 text-center transition sm:flex-row sm:gap-3 sm:bg-superficie sm:p-4 sm:text-left sm:shadow-suave sm:hover:-translate-y-0.5">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-full text-marca shadow-suave sm:size-12 sm:shadow-none {{ $acceso['tono'] }}">
                    <x-icono :nombre="$acceso['icono']" clase="size-6" />
                </span>
                <span class="min-w-0">
                    <span class="block font-display text-[13px] font-semibold sm:text-base"><span class="sm:hidden">{{ $acceso['corto'] }}</span><span class="hidden sm:inline">{{ $acceso['texto'] }}</span></span>
                    <span class="meta hidden lg:block">{{ $acceso['detalle'] }}</span>
                </span>
            </a>
        @endforeach
    </nav>

    @unless ($nuevoNegocio)
        <div class="mb-4 flex items-baseline justify-between gap-4">
            <h2 class="font-display text-[22px] font-semibold">Pedidos por atender</h2>
            <a href="{{ route('pedidos.index') }}" class="enlace text-sm">Ver todos →</a>
        </div>

        @if ($porAtender === 0)
            <section class="panel p-6 text-center">
                <p class="font-display text-lg font-semibold">Todo al día</p>
                <p class="mt-1 text-texto-2">Cuando registre un pedido aparecerá aquí, listo para avanzarlo.</p>
            </section>
        @else
            {{-- Teléfono: una etapa a la vez, con su número --}}
            <div class="mb-4 flex gap-2 overflow-x-auto lg:hidden" data-pestanas aria-label="Etapas">
                @foreach ($columnas as $columna)
                    @php $id = $columna['estado']->id_estado; $tono = $tonos[$etapas[$id][2]]; @endphp
                    <button type="button" data-pestana="{{ $id }}" aria-controls="etapa-{{ $id }}" aria-pressed="{{ $id === $pestanaInicial ? 'true' : 'false' }}"
                            class="flex min-h-10 flex-1 cursor-pointer items-center justify-center gap-1.5 rounded-xl bg-superficie px-3 text-sm font-extrabold whitespace-nowrap text-texto-2 shadow-suave aria-pressed:text-white {{ $tono['pestana'] }}">
                        {{ $id === EstadoPedido::LISTO ? 'Listos' : $etapas[$id][0] }}
                        <span class="tabular-nums">{{ $columna['total'] }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Escritorio: las tres etapas lado a lado, cada una con su color --}}
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                @foreach ($columnas as $columna)
                    @php $id = $columna['estado']->id_estado; $tono = $tonos[$etapas[$id][2]]; @endphp
                    <section id="etapa-{{ $id }}" data-etapa="{{ $id }}" aria-labelledby="titulo-etapa-{{ $id }}"
                             @class(['flex-col lg:rounded-2xl lg:p-3', $tono['fondo'] => true, 'max-lg:bg-transparent', 'flex' => $id === $pestanaInicial, 'hidden lg:flex' => $id !== $pestanaInicial])>
                        <header class="mb-3 hidden items-center gap-2 px-1 lg:flex">
                            <span class="size-2.5 rounded-full {{ $tono['punto'] }}" aria-hidden="true"></span>
                            <h2 id="titulo-etapa-{{ $id }}" class="font-sans text-sm font-extrabold">{{ $etapas[$id][0] }}</h2>
                            <span class="ml-auto rounded-full px-2.5 text-xs leading-5 font-extrabold text-white tabular-nums {{ $tono['punto'] }}">{{ $columna['total'] }}</span>
                        </header>

                        <ul class="flex flex-col gap-3">
                            @forelse ($columna['pedidos'] as $pedido)
                                <li class="overflow-hidden rounded-xl border-t-4 bg-superficie shadow-suave {{ $tono['borde'] }}">
                                    <a href="{{ route('pedidos.show', $pedido) }}" class="block px-4 pt-3 pb-2">
                                        <span class="flex items-baseline justify-between gap-3">
                                            <span class="truncate font-extrabold">{{ $pedido->cliente->nombre }}</span>
                                            <x-moneda :valor="$pedido->total" class="font-extrabold" />
                                        </span>
                                        <span class="meta mt-0.5 block">
                                            #{{ $pedido->numero() }} · {{ $pedido->fecha->diffForHumans() }} · {{ (int) $pedido->unidades }} {{ (int) $pedido->unidades === 1 ? 'producto' : 'productos' }}
                                        </span>
                                    </a>
                                    <form method="POST" action="{{ route('pedidos.estado', $pedido) }}" class="px-4 pb-3.5" data-envio-unico>
                                        @csrf
                                        <button type="submit" name="id_estado" value="{{ $columna['siguiente']->id_estado }}" data-texto-envio="Actualizando…"
                                                class="flex min-h-10 w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl text-sm font-extrabold transition-colors disabled:cursor-wait disabled:opacity-60 {{ $tono['boton'] }}">
                                            {{ $columna['siguiente']->accion() }}
                                            <x-icono nombre="flecha-derecha" clase="size-4" />
                                        </button>
                                    </form>
                                </li>
                            @empty
                                <li class="rounded-xl border-2 border-dashed border-stone-300 px-4 py-8 text-center text-sm text-texto-2">{{ $etapas[$id][1] }}</li>
                            @endforelse
                        </ul>

                        @if ($columna['total'] > $columna['pedidos']->count())
                            <a href="{{ route('pedidos.index', ['estado' => $id]) }}" class="enlace mt-3 px-1 text-sm">
                                Ver los {{ $columna['total'] }} pedidos
                            </a>
                        @endif
                    </section>
                @endforeach
            </div>
        @endif
    @endunless
</x-layouts.app>
