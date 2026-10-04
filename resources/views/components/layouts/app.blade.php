@props(['titulo' => 'Panel', 'subtitulo' => null, 'ruta' => []])
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
    <meta name="theme-color" content="#F7F7F5">
    <title>{{ $titulo }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased">
    <a href="#contenido" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:rounded-lg focus:bg-white focus:px-3 focus:py-2 focus:shadow-flotante">Saltar al contenido</a>

    {{-- ===== Menú lateral (escritorio) ===== --}}
    @unless ($esCliente)
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-58 flex-col border-r border-borde bg-superficie-2 lg:flex print:hidden">
            <div class="px-5 pt-6 pb-5">
                <a href="{{ route('panel') }}" class="rounded-lg"><x-marca /></a>
            </div>

            @if ($crearPedido)
                <div class="px-3 pb-4">
                    <a href="{{ route('pedidos.create') }}" class="btn btn-primario w-full"><x-icono nombre="mas" clase="size-4" /> Crear pedido</a>
                </div>
            @endif

            <nav class="flex-1 space-y-0.5 overflow-y-auto px-3" aria-label="Menú principal">
                @foreach ($menu as $item)
                    <a href="{{ route($item['ruta']) }}" @if ($item['actual']) aria-current="page" @endif
                       @class([
                           'flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors',
                           'bg-superficie font-medium text-texto shadow-[inset_2px_0_0_var(--color-marca)]' => $item['actual'],
                           'text-texto-2 hover:bg-stone-200/50 hover:text-texto' => ! $item['actual'],
                       ])>
                        <x-icono :nombre="$item['icono']" :clase="'size-5 '.($item['actual'] ? 'text-marca' : '')" />
                        {{ $item['texto'] }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-borde p-3">
                <a href="{{ route('perfil.edit') }}" @class(['flex items-center gap-3 rounded-lg px-2 py-2 transition-colors hover:bg-stone-200/50', 'bg-superficie' => request()->routeIs('perfil.*')])
                   @if (request()->routeIs('perfil.*')) aria-current="page" @endif>
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-marca-100 text-[13px] font-semibold text-marca">{{ $usuario->iniciales() }}</span>
                    <span class="min-w-0 leading-tight">
                        <span class="block truncate text-sm font-medium">{{ $usuario->nombre }}</span>
                        <span class="block truncate text-[13px] text-texto-2">{{ $usuario->negocio ?: $usuario->rol->nombre }}</span>
                    </span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mt-1 flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-sm text-texto-2 transition-colors hover:bg-stone-200/50 hover:text-texto">
                        <x-icono nombre="salir" /> Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>
    @endunless

    <div @class(['flex min-h-full flex-col print:pl-0', 'lg:pl-58' => ! $esCliente])>
        {{-- ===== Barra superior: en el teléfono para todos; en escritorio solo para el cliente (no tiene menú) ===== --}}
        <header @class([
            'sticky top-0 z-20 border-b border-borde bg-fondo/95 pt-[env(safe-area-inset-top)] backdrop-blur print:hidden',
            'lg:hidden' => ! $esCliente,
        ])>
            <div @class(['mx-auto flex h-14 items-center justify-between gap-3 px-4', 'max-w-3xl sm:px-6' => $esCliente])>
                <a href="{{ route('panel') }}" class="rounded-lg"><x-marca /></a>

                @if ($esCliente)
                    <details class="menu relative" data-menu>
                        <summary class="flex cursor-pointer items-center gap-2 rounded-lg py-1 pr-1 pl-2 text-sm text-texto-2 hover:bg-superficie-2 hover:text-texto">
                            <span class="hidden sm:inline">{{ strtok($usuario->nombre, ' ') }}</span>
                            <span class="flex size-8 items-center justify-center rounded-full bg-marca-100 text-[13px] font-semibold text-marca">{{ $usuario->iniciales() }}</span>
                            <span class="sr-only">Abrir menú de la cuenta</span>
                        </summary>
                        <div class="menu-lista">
                            <a href="{{ route('perfil.edit') }}" class="menu-opcion"><x-icono nombre="usuario" clase="size-4 text-texto-2" /> Mi perfil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="menu-opcion"><x-icono nombre="salir" clase="size-4 text-texto-2" /> Cerrar sesión</button>
                            </form>
                        </div>
                    </details>
                @endif
            </div>
        </header>

        <main id="contenido" @class([
            'mx-auto w-full flex-1 px-4 pt-6 sm:px-6',
            'max-w-[1200px] pb-28 lg:px-10 lg:pt-8 lg:pb-12' => ! $esCliente,
            'max-w-3xl pb-12' => $esCliente,
        ])>
            {{-- Encabezado: ruta corta para volver, título y la acción principal de la pantalla --}}
            <div class="mb-6 flex items-start justify-between gap-4 sm:items-end">
                <div class="min-w-0 flex-1">
                    @if ($ruta)
                        <nav aria-label="Ruta" class="mb-1 text-sm text-texto-2">
                            @foreach ($ruta as $texto => $url)
                                <a href="{{ $url }}" class="hover:text-texto hover:underline">{{ $texto }}</a>
                                <span aria-hidden="true" class="mx-1">/</span>
                            @endforeach
                        </nav>
                    @endif
                    <h1 class="text-2xl font-semibold tracking-tight text-balance text-texto">{{ $titulo }}</h1>
                    @if ($subtitulo)<p class="mt-1 text-texto-2">{{ $subtitulo }}</p>@endif
                </div>
                @isset($acciones)
                    <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">{{ $acciones }}</div>
                @endisset
            </div>

            <x-alertas :separacion="$esCliente ? 'bottom-6' : 'bottom-24 lg:bottom-6'" />
            {{ $slot }}
        </main>

        <footer @class(['px-6 pb-6 text-center text-[13px] text-texto-2 print:hidden', 'hidden lg:block' => ! $esCliente])>
            {{ config('app.name') }} · Universidad Mariano Gálvez, sede Jutiapa
        </footer>
    </div>

    {{-- ===== Barra inferior (teléfono): accesos principales al alcance del pulgar ===== --}}
    @unless ($esCliente)
        <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-borde bg-superficie pb-[env(safe-area-inset-bottom)] lg:hidden print:hidden" aria-label="Menú principal">
            <ul class="mx-auto flex max-w-md items-stretch justify-around">
                @foreach ($enBarra->take($crearPedido ? 2 : 3) as $item)
                    @include('components.layouts.partials.boton-barra', ['item' => $item])
                @endforeach

                @if ($crearPedido)
                    <li class="flex flex-1 justify-center">
                        <a href="{{ route('pedidos.create') }}" class="flex min-h-16 flex-col items-center justify-center gap-1 px-2 text-[11px] font-medium text-marca">
                            <span class="flex size-9 items-center justify-center rounded-lg bg-marca text-white"><x-icono nombre="mas" clase="size-5" /></span>
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
                            'flex min-h-16 w-full cursor-pointer flex-col items-center justify-center gap-1 px-2 text-[11px] font-medium',
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
