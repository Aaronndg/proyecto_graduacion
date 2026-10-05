@props(['titulo' => 'Panel', 'subtitulo' => null, 'ruta' => [], 'encabezado' => true])
@php
    $usuario = auth()->user();
    $esCliente = $usuario->esCliente();

    // Navegación basada en roles (Figura 42): cada usuario ve solo las opciones autorizadas.
    // «movil»: dónde aparece en el teléfono (barra inferior o la hoja «Más»).
    $menu = collect([
        ['ruta' => 'panel', 'activa' => 'panel', 'texto' => $usuario->esEmprendedor() ? 'Hoy' : 'Inicio', 'icono' => 'inicio', 'roles' => ['administrador', 'emprendedor'], 'movil' => 'barra'],
        ['ruta' => 'pedidos.index', 'activa' => 'pedidos.*', 'texto' => 'Pedidos', 'icono' => 'pedido', 'roles' => ['emprendedor'], 'movil' => 'barra'],
        ['ruta' => 'clientes.index', 'activa' => 'clientes.*', 'texto' => 'Clientes', 'icono' => 'usuarios', 'roles' => ['emprendedor'], 'movil' => 'barra'],
        ['ruta' => 'productos.index', 'activa' => 'productos.*', 'texto' => 'Productos', 'icono' => 'producto', 'roles' => ['emprendedor'], 'movil' => 'mas'],
        ['ruta' => 'reportes', 'activa' => 'reportes*', 'texto' => 'Reportes', 'icono' => 'reportes', 'roles' => ['emprendedor'], 'movil' => 'mas'],
        ['ruta' => 'admin.usuarios.index', 'activa' => 'admin.usuarios.*', 'texto' => 'Usuarios', 'icono' => 'usuarios', 'roles' => ['administrador'], 'movil' => 'barra'],
    ])
        ->filter(fn ($item) => $usuario->tieneRol(...$item['roles']) && Route::has($item['ruta']))
        ->map(fn ($item) => $item + ['actual' => request()->routeIs(...(array) $item['activa'])]);

    $enBarra = $menu->where('movil', 'barra');
    $enMas = $menu->where('movil', 'mas');
    $masActiva = $enMas->contains('actual', true) || request()->routeIs('perfil.*');
    $crearPedido = $usuario->esEmprendedor() && Route::has('pedidos.create');
@endphp
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0B2452">
    <title>{{ $titulo }} · {{ config('app.name') }}</title>
    @include('components.layouts.partials.fuentes')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased">
    <a href="#contenido" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:rounded-lg focus:bg-white focus:px-3 focus:py-2 focus:shadow-flotante">Saltar al contenido</a>

    <div class="flex min-h-full flex-col">
        {{-- ===== Franja azul: marca, búsqueda, «Crear pedido», cuenta y el menú (escritorio) ===== --}}
        <header class="banda bg-marca pt-[env(safe-area-inset-top)] text-white print:hidden">
            <div @class(['mx-auto px-4 sm:px-6', 'max-w-[1200px] lg:px-10' => ! $esCliente, 'max-w-3xl' => $esCliente])>
                <div class="flex h-16 items-center gap-4">
                    <a href="{{ route('panel') }}" class="shrink-0 rounded-xl"><x-marca claro :detalle="$usuario->negocio" /></a>

                    @if ($crearPedido)
                        <form method="GET" action="{{ route('pedidos.index') }}" role="search" class="relative hidden max-w-xl flex-1 md:block">
                            <label for="buscar-global" class="sr-only">Buscar pedidos</label>
                            <x-icono nombre="buscar" clase="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-texto-2" />
                            <input id="buscar-global" name="buscar" type="search" placeholder="Buscar pedido o cliente…" value="{{ request()->routeIs('pedidos.index') ? request('buscar') : '' }}"
                                   class="h-10 w-full rounded-xl border-0 bg-white pr-3 pl-11 text-sm text-texto placeholder:text-texto-2 focus:ring-3 focus:ring-oro/60 focus:outline-none">
                        </form>
                    @endif

                    <div class="ml-auto flex items-center gap-3">
                        @if ($crearPedido)
                            <a href="{{ route('pedidos.create') }}" class="btn btn-primario hidden lg:inline-flex"><x-icono nombre="mas" clase="size-4" /> Crear pedido</a>
                        @endif

                        <details class="menu relative" data-menu>
                            <summary class="flex cursor-pointer items-center gap-2 rounded-full p-1 text-sm font-bold text-white hover:bg-white/10 sm:pl-3">
                                <span class="hidden sm:inline">{{ strtok($usuario->nombre, ' ') }}</span>
                                <span class="flex size-9 items-center justify-center rounded-full bg-oro text-[13px] font-extrabold text-stone-950">{{ $usuario->iniciales() }}</span>
                                <span class="sr-only">Abrir menú de la cuenta</span>
                            </summary>
                            <div class="menu-lista text-texto">
                                <p class="border-b border-borde px-3.5 pt-2 pb-2.5">
                                    <span class="block text-sm font-bold">{{ $usuario->nombre }}</span>
                                    <span class="block truncate text-[13px] text-texto-2">{{ $usuario->correo }}</span>
                                </p>
                                <a href="{{ route('perfil.edit') }}" class="menu-opcion"><x-icono nombre="usuario" clase="size-4 text-texto-2" /> Mi perfil</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="menu-opcion"><x-icono nombre="salir" clase="size-4 text-texto-2" /> Cerrar sesión</button>
                                </form>
                            </div>
                        </details>
                    </div>
                </div>

                @if ($crearPedido)
                    {{-- Búsqueda en el teléfono: debajo de la marca --}}
                    <form method="GET" action="{{ route('pedidos.index') }}" role="search" class="relative pb-3 md:hidden">
                        <label for="buscar-movil" class="sr-only">Buscar pedidos</label>
                        <x-icono nombre="buscar" clase="pointer-events-none absolute top-5 left-3.5 size-5 -translate-y-1/2 text-texto-2" />
                        <input id="buscar-movil" name="buscar" type="search" placeholder="Buscar pedido o cliente…"
                               class="h-10 w-full rounded-xl border-0 bg-white pr-3 pl-11 text-base text-texto placeholder:text-texto-2 focus:ring-3 focus:ring-oro/60 focus:outline-none">
                    </form>
                @endif

                {{-- Menú principal (escritorio): navegación basada en roles --}}
                @unless ($esCliente)
                    <nav class="-mx-1 hidden gap-1 pb-3 lg:flex" aria-label="Menú principal">
                        @foreach ($menu as $item)
                            <a href="{{ route($item['ruta']) }}" @if ($item['actual']) aria-current="page" @endif
                               @class([
                                   'flex items-center gap-2 rounded-xl px-3.5 py-2 text-sm font-bold transition-colors',
                                   'bg-white text-marca' => $item['actual'],
                                   'text-stone-300 hover:bg-white/10 hover:text-white' => ! $item['actual'],
                               ])>
                                <x-icono :nombre="$item['icono']" clase="size-5" />
                                {{ $item['texto'] }}
                            </a>
                        @endforeach
                    </nav>
                @endunless
            </div>
        </header>
        <div class="textil print:hidden" aria-hidden="true"></div>

        <main id="contenido" @class([
            'mx-auto w-full flex-1 px-4 pt-6 sm:px-6',
            'max-w-[1200px] pb-28 lg:px-10 lg:pt-8 lg:pb-12' => ! $esCliente,
            'max-w-3xl pb-12' => $esCliente,
        ])>
            {{-- Encabezado: ruta corta para volver, título y la acción principal de la pantalla («Hoy» lo lleva en su banner) --}}
            @if ($encabezado)
                <div class="mb-6 flex items-start justify-between gap-4 sm:items-end">
                    <div class="min-w-0 flex-1">
                        @if ($ruta)
                            <nav aria-label="Ruta" class="mb-1 text-sm font-bold text-texto-2">
                                @foreach ($ruta as $texto => $url)
                                    <a href="{{ $url }}" class="hover:text-marca hover:underline">{{ $texto }}</a>
                                    <span aria-hidden="true" class="mx-1 text-stone-300">/</span>
                                @endforeach
                            </nav>
                        @endif
                        <h1 class="text-[26px] leading-tight font-bold text-balance text-texto sm:text-[30px]">{{ $titulo }}</h1>
                        @if ($subtitulo)<p class="mt-1 text-texto-2">{{ $subtitulo }}</p>@endif
                    </div>
                    @isset($acciones)
                        <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">{{ $acciones }}</div>
                    @endisset
                </div>
            @endif

            <x-alertas :separacion="$esCliente ? 'bottom-6' : 'bottom-24 lg:bottom-6'" />
            {{ $slot }}
        </main>

        <footer @class(['px-6 pb-6 text-center text-[13px] text-texto-2 print:hidden', 'hidden lg:block' => ! $esCliente])>
            {{ config('app.name') }} · Universidad Mariano Gálvez, sede Jutiapa
        </footer>
    </div>

    {{-- ===== Barra inferior (teléfono): accesos principales al alcance del pulgar ===== --}}
    @unless ($esCliente)
        <nav class="fixed inset-x-0 bottom-0 z-30 rounded-t-2xl bg-superficie pb-[env(safe-area-inset-bottom)] shadow-[0_-4px_18px_rgb(16_41_69/0.10)] lg:hidden print:hidden" aria-label="Menú principal">
            <ul class="mx-auto flex max-w-md items-stretch justify-around">
                @foreach ($enBarra->take($crearPedido ? 2 : 3) as $item)
                    @include('components.layouts.partials.boton-barra', ['item' => $item])
                @endforeach

                @if ($crearPedido)
                    <li class="flex flex-1 justify-center">
                        <a href="{{ route('pedidos.create') }}" class="flex min-h-16 flex-col items-center justify-center px-2">
                            <span class="-mt-6 flex size-13 items-center justify-center rounded-full bg-oro text-stone-950 shadow-oro ring-4 ring-superficie"><x-icono nombre="mas" clase="size-6" /></span>
                            <span class="sr-only">Crear pedido</span>
                        </a>
                    </li>
                    @foreach ($enBarra->slice(2) as $item)
                        @include('components.layouts.partials.boton-barra', ['item' => $item])
                    @endforeach
                @endif

                {{-- «Más»: el resto de las secciones, el perfil y cerrar sesión --}}
                <li class="flex flex-1">
                    <details class="menu w-full" data-menu>
                        <summary @class([
                            'flex min-h-16 w-full cursor-pointer flex-col items-center justify-center gap-1 px-2 text-[11px] font-bold',
                            'text-marca' => $masActiva,
                            'text-texto-2' => ! $masActiva,
                        ])>
                            <x-icono nombre="menu" clase="size-6" />
                            Más
                        </summary>
                        <div class="fixed inset-x-3 bottom-[calc(4.5rem+env(safe-area-inset-bottom))] z-40 rounded-xl border border-borde bg-superficie py-2 shadow-flotante">
                            @foreach ($enMas as $item)
                                <a href="{{ route($item['ruta']) }}" @if ($item['actual']) aria-current="page" @endif
                                   @class(['menu-opcion py-3', 'font-medium text-marca' => $item['actual']])>
                                    <x-icono :nombre="$item['icono']" clase="size-5 text-texto-2" /> {{ $item['texto'] }}
                                </a>
                            @endforeach
                            @if ($enMas->isNotEmpty())<div class="my-1 border-t border-borde"></div>@endif
                            <a href="{{ route('perfil.edit') }}" class="menu-opcion py-3"><x-icono nombre="usuario" clase="size-5 text-texto-2" /> Mi perfil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="menu-opcion py-3"><x-icono nombre="salir" clase="size-5 text-texto-2" /> Cerrar sesión</button>
                            </form>
                        </div>
                    </details>
                </li>
            </ul>
        </nav>
    @endunless

    {{-- ===== Diálogo de confirmación (reemplaza el cuadro nativo del navegador) ===== --}}
    <dialog id="dialogo-confirmar" class="dialogo m-auto w-[calc(100%-2rem)] max-w-md rounded-xl border border-borde bg-superficie p-0 text-texto shadow-flotante"
            aria-labelledby="dialogo-titulo" aria-describedby="dialogo-texto">
        <form method="dialog" class="p-6">
            <h2 id="dialogo-titulo" class="titulo-seccion">¿Está seguro?</h2>
            <p id="dialogo-texto" class="mt-2 text-sm text-texto-2"></p>
            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="submit" value="no" class="btn btn-secundario">Volver</button>
                <button type="submit" value="si" class="btn btn-primario" data-dialogo-aceptar>Confirmar</button>
            </div>
        </form>
    </dialog>
</body>
</html>
