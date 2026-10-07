@php
    use App\Models\EstadoPedido;

    // Nombre, texto vacío y color de cada etapa (el mismo de su etiqueta de estado).
    $etapas = [
        EstadoPedido::NUEVO => ['Nuevos', 'Sin pedidos nuevos', 'sky'],
        EstadoPedido::EN_PROCESO => ['En proceso', 'Nada en preparación', 'amber'],
        EstadoPedido::LISTO => ['Listos para entregar', 'Nada listo por entregar', 'emerald'],
    ];
    $tonos = [
        'sky' => ['fondo' => 'lg:cristal lg:border lg:border-white/10 lg:shadow-suave', 'punto' => 'bg-sky-500 shadow-[0_0_12px_2px_rgb(76_141_255/0.6)]', 'borde' => 'border-t-sky-500', 'boton' => 'bg-sky-50 text-sky-700 hover:bg-sky-100', 'pestana' => 'aria-pressed:bg-sky-500'],
        'amber' => ['fondo' => 'lg:cristal lg:border lg:border-white/10 lg:shadow-suave', 'punto' => 'bg-amber-500 shadow-[0_0_12px_2px_rgb(224_176_79/0.55)]', 'borde' => 'border-t-amber-500', 'boton' => 'bg-amber-50 text-amber-700 hover:bg-amber-100', 'pestana' => 'aria-pressed:bg-amber-500'],
        'emerald' => ['fondo' => 'lg:cristal lg:border lg:border-white/10 lg:shadow-suave', 'punto' => 'bg-emerald-500 shadow-[0_0_12px_2px_rgb(63_191_138/0.55)]', 'borde' => 'border-t-emerald-500', 'boton' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100', 'pestana' => 'aria-pressed:bg-emerald-500'],
    ];
    $listos = $columnas->firstWhere('estado.id_estado', EstadoPedido::LISTO)['total'];
    // En el teléfono se abre la primera pestaña que tenga pedidos.
    $pestanaInicial = $columnas->first(fn ($c) => $c['total'] > 0)['estado']->id_estado ?? EstadoPedido::NUEVO;
    $enlaceVentas = route('reportes', ['desde' => $ventas['desde']->toDateString(), 'hasta' => today()->toDateString()]);
    $hora = now()->hour;
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
    $nuevoNegocio = ! $tieneProductos && $porAtender === 0;
    $accesos = [
        ['ruta' => route('pedidos.create'), 'icono' => 'pedido', 'texto' => 'Nuevo pedido', 'corto' => 'Pedido', 'detalle' => 'Registre lo que le pidieron', 'tono' => 'bg-oro-suave'],
        ['ruta' => route('clientes.index'), 'icono' => 'clientes', 'texto' => 'Clientes', 'corto' => 'Clientes', 'detalle' => 'Quiénes le compran', 'tono' => 'bg-marca-100'],
        ['ruta' => route('productos.index'), 'icono' => 'productos', 'texto' => 'Productos', 'corto' => 'Productos', 'detalle' => 'Lo que vende y su precio', 'tono' => 'bg-oro-suave'],
        ['ruta' => route('reportes'), 'icono' => 'reportes', 'texto' => 'Reportes', 'corto' => 'Reportes', 'detalle' => 'Ventas y lo más vendido', 'tono' => 'bg-marca-100'],
    ];
@endphp
<x-layouts.app titulo="Hoy" :encabezado="false">

    {{-- Banner de bienvenida: el saludo y lo pendiente del día, sobre la imagen de NEXO --}}
    <x-bienvenida :titulo="'¡'.$saludo.', '.strtok(auth()->user()->nombre, ' ').'!'" :antetitulo="ucfirst(today()->translatedFormat('l j \\d\\e F'))">
        @if ($nuevoNegocio)
            <b class="text-white">Empiece agregando lo que vende.</b> Con sus productos registrados podrá crear pedidos en segundos y verlos aquí, ordenados por etapa.
        @elseif ($porAtender === 0)
            No tiene pedidos pendientes.
        @else
            Tiene <b class="text-white">{{ $porAtender }} {{ $porAtender === 1 ? 'pedido' : 'pedidos' }} por atender</b>{{ $listos ? ' y '.$listos.' '.($listos === 1 ? 'listo' : 'listos').' para entregar' : '' }}.
        @endif

        <x-slot:acciones>
            @if ($nuevoNegocio)
                <a href="{{ route('productos.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Agregar producto</a>
            @else
                <a href="{{ route('pedidos.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Crear pedido</a>
                <a href="{{ route('pedidos.index') }}" class="btn border-2 border-white/70 text-white hover:bg-white/10">Ver todos los pedidos</a>
            @endif
        </x-slot:acciones>

        @unless ($nuevoNegocio)
            <x-slot:pie>
                <p class="inline-flex flex-wrap items-center gap-x-1.5 rounded-full bg-white/10 px-3.5 py-1.5 text-sm backdrop-blur-sm">
                    @if ($ventas['entregas'])
                        En los últimos 7 días vendió
                        <a href="{{ $enlaceVentas }}" class="font-extrabold text-[#E9C46A] tabular-nums hover:underline">Q {{ number_format($ventas['monto'], 2) }}</a>
                        en {{ $ventas['entregas'] }} {{ $ventas['entregas'] === 1 ? 'entrega' : 'entregas' }}.
                    @else
                        Sin ventas en los últimos 7 días.
                    @endif
                </p>
            </x-slot:pie>
        @endunless
    </x-bienvenida>

    {{-- Accesos en mosaico (Bento): fichas con relieve; «Nuevo pedido» destaca en dorado --}}
    <nav class="mb-8 grid grid-cols-4 gap-2 sm:gap-4" aria-label="Accesos rápidos">
        @foreach ($accesos as $acceso)
            <a href="{{ $acceso['ruta'] }}" @class([
                'group flex flex-col items-center justify-between gap-2 rounded-2xl px-1 py-3 text-center transition hover:-translate-y-0.5 active:translate-y-px sm:items-start sm:gap-5 sm:p-5 sm:text-left',
                'tarjeta-oro' => $loop->first,
                'relieve cristal border border-white/10' => ! $loop->first,
            ])>
                <span @class(['flex size-11 shrink-0 items-center justify-center rounded-xl sm:size-12', 'bg-linear-to-br from-[#2A2116] to-[#0E0B07] shadow-[inset_0_1px_0_rgb(255_255_255/0.15),0_6px_12px_-4px_rgb(0_0_0/0.5)]' => $loop->first, 'ficha-3d' => ! $loop->first])>
                    <x-icono-duo :nombre="$acceso['icono']" clase="size-6" />
                </span>
                <span class="min-w-0">
                    <span class="block font-display text-[12.5px] font-semibold sm:text-[17px]"><span class="sm:hidden">{{ $acceso['corto'] }}</span><span class="hidden sm:inline">{{ $acceso['texto'] }}</span></span>
                    <span @class(['hidden text-[13px] lg:block', 'text-[#5A4419]' => $loop->first, 'text-texto-2' => ! $loop->first])>{{ $acceso['detalle'] }}</span>
                </span>
            </a>
        @endforeach
    </nav>

    @unless ($nuevoNegocio)
        <div class="mb-4 flex items-baseline justify-between gap-4">
            <h2 class="text-[22px] font-bold">Pedidos por atender</h2>
            <a href="{{ route('pedidos.index') }}" class="enlace text-sm">Ver todos →</a>
        </div>

        @if ($porAtender === 0)
            <section class="panel flex flex-col items-center p-6 text-center">
                <x-ilustracion nombre="libreta" class="mb-2" />
                <p class="text-lg font-bold">Todo al día</p>
                <p class="mt-1 text-texto-2">Cuando registre un pedido aparecerá aquí, listo para avanzarlo.</p>
            </section>
        @else
            {{-- Teléfono: una etapa a la vez, con su número --}}
            <div class="mb-4 flex gap-2 overflow-x-auto lg:hidden" data-pestanas aria-label="Etapas">
                @foreach ($columnas as $columna)
                    @php $id = $columna['estado']->id_estado; $tono = $tonos[$etapas[$id][2]]; @endphp
                    <button type="button" data-pestana="{{ $id }}" aria-controls="etapa-{{ $id }}" aria-pressed="{{ $id === $pestanaInicial ? 'true' : 'false' }}"
                            class="flex min-h-10 flex-1 cursor-pointer items-center justify-center gap-1.5 rounded-xl bg-superficie px-3 text-sm font-extrabold whitespace-nowrap text-texto-2 relieve transition active:translate-y-px aria-pressed:text-tinta-oro {{ $tono['pestana'] }}">
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
                            <h2 id="titulo-etapa-{{ $id }}" class="text-sm font-extrabold">{{ $etapas[$id][0] }}</h2>
                            <span class="ml-auto rounded-full px-2.5 text-xs leading-5 font-extrabold text-tinta-oro tabular-nums {{ $tono['punto'] }}">{{ $columna['total'] }}</span>
                        </header>

                        <ul class="flex flex-col gap-3">
                            @forelse ($columna['pedidos'] as $pedido)
                                <li class="overflow-hidden rounded-2xl border border-t-2 cristal-claro shadow-suave transition hover:-translate-y-0.5 hover:shadow-flotante {{ $tono['borde'] }}">
                                    @php $productosPedido = $pedido->detalles->pluck('producto')->filter()->unique('id_producto'); @endphp
                                    <a href="{{ route('pedidos.show', $pedido) }}" class="block px-4 pt-3 pb-2.5">
                                        <span class="flex items-center gap-3">
                                            <x-avatar :nombre="$pedido->cliente->nombre" />
                                            <span class="min-w-0 flex-1">
                                                <span class="flex items-baseline justify-between gap-3">
                                                    <span class="truncate font-bold">{{ $pedido->cliente->nombre }}</span>
                                                    <x-moneda :valor="$pedido->total" class="font-bold" />
                                                </span>
                                                <span class="meta block">
                                                    #{{ $pedido->numero() }} · {{ $pedido->fecha->diffForHumans() }} · {{ (int) $pedido->unidades }} {{ (int) $pedido->unidades === 1 ? 'producto' : 'productos' }}
                                                </span>
                                                <x-entrega :pedido="$pedido" class="mt-1.5" />
                                            </span>
                                        </span>
                                        {{-- Miniaturas de lo que pidió (solo si algún producto tiene foto) --}}
                                        @if ($productosPedido->contains(fn ($p) => $p->imagen))
                                            <span class="mt-2.5 flex items-center gap-1.5">
                                                @foreach ($productosPedido->take(4) as $producto)
                                                    <span class="size-9 overflow-hidden rounded-lg"><x-foto-producto :producto="$producto" icono="size-4" /></span>
                                                @endforeach
                                                @if ($productosPedido->count() > 4)
                                                    <span class="flex h-9 items-center rounded-lg bg-superficie-2 px-2 text-xs font-bold text-texto-2">+{{ $productosPedido->count() - 4 }}</span>
                                                @endif
                                            </span>
                                        @endif
                                    </a>
                                    @if ($id === EstadoPedido::LISTO && $aviso = $pedido->cliente->avisoPedidoListo($pedido, auth()->user()->negocio ?? auth()->user()->nombre))
                                        <a href="{{ $aviso }}" target="_blank" rel="noopener" class="mx-4 mb-2 flex items-center justify-center gap-1.5 rounded-xl py-1.5 text-sm font-bold text-emerald-700 hover:bg-emerald-50">
                                            <x-icono nombre="whatsapp" clase="size-4" /> Avisar que está listo
                                        </a>
                                    @endif
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
                                <li class="flex flex-col items-center rounded-xl border-2 border-dashed border-white/15 px-4 py-6 text-center text-sm text-texto-2">
                                    <x-ilustracion nombre="libreta" class="mb-1 h-16 w-20" />
                                    {{ $etapas[$id][1] }}
                                </li>
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
