@props(['titulo' => 'Panel', 'subtitulo' => null])
@php
    $usuario = auth()->user();

    // Navegación basada en roles (Figura 42): cada usuario ve solo las opciones autorizadas.
    $menu = collect([
        ['ruta' => 'panel', 'activa' => $usuario->esCliente() ? ['panel', 'mis-pedidos.*'] : 'panel', 'texto' => $usuario->esCliente() ? 'Mis pedidos' : 'Inicio', 'icono' => 'inicio', 'roles' => ['administrador', 'emprendedor', 'cliente']],
        ['ruta' => 'pedidos.index', 'activa' => 'pedidos.*', 'texto' => 'Pedidos', 'icono' => 'pedido', 'roles' => ['emprendedor']],
        ['ruta' => 'seguimiento', 'activa' => 'seguimiento', 'texto' => 'Seguimiento', 'icono' => 'seguimiento', 'roles' => ['emprendedor']],
        ['ruta' => 'clientes.index', 'activa' => 'clientes.*', 'texto' => 'Clientes', 'icono' => 'usuarios', 'roles' => ['emprendedor']],
        ['ruta' => 'productos.index', 'activa' => 'productos.*', 'texto' => 'Productos', 'icono' => 'producto', 'roles' => ['emprendedor']],
        ['ruta' => 'reportes', 'activa' => 'reportes*', 'texto' => 'Reportes', 'icono' => 'reportes', 'roles' => ['emprendedor']],
        ['ruta' => 'admin.usuarios.index', 'activa' => 'admin.usuarios.*', 'texto' => 'Usuarios', 'icono' => 'usuarios', 'roles' => ['administrador']],
    ])->filter(fn ($item) => $usuario->tieneRol(...$item['roles']) && Route::has($item['ruta']));
@endphp
<!DOCTYPE html>
<html lang="es" class="h-full bg-stone-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a8076">
    <title>{{ $titulo }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-stone-800 antialiased">
    <a href="#contenido" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:rounded-lg focus:bg-white focus:px-3 focus:py-2 focus:shadow">Saltar al contenido</a>

    {{-- Fondo del menú en móvil --}}
    <div id="menu-fondo" class="print:hidden fixed inset-0 z-30 hidden bg-stone-900/40 backdrop-blur-sm lg:hidden"></div>

    {{-- Menú lateral --}}
    <aside id="menu-lateral" class="print:hidden fixed inset-y-0 left-0 z-40 flex w-68 -translate-x-full flex-col border-r border-stone-200 bg-white transition-transform duration-200 lg:translate-x-0">
        <div class="flex h-18 items-center px-5">
            <a href="{{ route('panel') }}"><x-marca /></a>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Menú principal">
            @foreach ($menu as $item)
                @php $activa = request()->routeIs(...(array) $item['activa']); @endphp
                <a href="{{ route($item['ruta']) }}" @if ($activa) aria-current="page" @endif
                   @class([
                       'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                       'bg-marca-50 text-marca-800' => $activa,
                       'text-stone-600 hover:bg-stone-100 hover:text-stone-900' => ! $activa,
                   ])>
                    <x-icono :nombre="$item['icono']" :clase="'size-5 '.($activa ? 'text-marca-600' : 'text-stone-400 group-hover:text-stone-600')" />
                    {{ $item['texto'] }}
                </a>
            @endforeach
        </nav>

        {{-- Usuario --}}
        <div class="border-t border-stone-200 p-3">
            <a href="{{ route('perfil.edit') }}" @class(['flex items-center gap-3 rounded-xl p-2 transition hover:bg-stone-100', 'bg-stone-100' => request()->routeIs('perfil.*')])>
                <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-marca-100 text-sm font-semibold text-marca-800">{{ $usuario->iniciales() }}</span>
                <span class="min-w-0 leading-tight">
                    <span class="block truncate text-sm font-semibold text-stone-800">{{ $usuario->nombre }}</span>
                    <span class="block truncate text-xs text-stone-500">{{ $usuario->negocio ?: $usuario->rol->nombre }}</span>
                </span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit" class="flex w-full cursor-pointer items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-stone-500 transition hover:bg-stone-100 hover:text-stone-900">
                    <x-icono nombre="salir" /> Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    <div class="flex min-h-full flex-col lg:pl-68 print:pl-0">
        {{-- Barra superior en móvil --}}
        <header class="print:hidden sticky top-0 z-20 flex h-16 items-center justify-between border-b border-stone-200 bg-white/90 px-4 backdrop-blur lg:hidden">
            <a href="{{ route('panel') }}"><x-marca /></a>
            <button type="button" data-menu-toggle aria-controls="menu-lateral" aria-expanded="false" class="rounded-xl p-2 text-stone-600 hover:bg-stone-100">
                <x-icono nombre="menu" clase="size-6" />
                <span class="sr-only">Abrir menú</span>
            </button>
        </header>

        <main id="contenido" class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <h1 class="text-2xl font-bold tracking-tight text-stone-900 sm:text-[28px]">{{ $titulo }}</h1>
                    @if ($subtitulo)<p class="mt-1 text-stone-500">{{ $subtitulo }}</p>@endif
                </div>
                @isset($acciones)
                    <div class="flex flex-wrap gap-2">{{ $acciones }}</div>
                @endisset
            </div>

            <x-alertas />
            {{ $slot }}
        </main>

        <footer class="px-6 pb-6 text-center text-xs text-stone-400">
            {{ config('app.name') }} · Universidad Mariano Gálvez, sede Jutiapa
        </footer>
    </div>
</body>
</html>
