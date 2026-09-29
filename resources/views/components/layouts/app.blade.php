@props(['titulo' => 'Panel'])
@php
    $usuario = auth()->user();

    // Navegación basada en roles (Figura 42): cada usuario ve solo las opciones autorizadas.
    $menu = collect([
        ['ruta' => 'panel', 'activa' => 'panel', 'texto' => $usuario->esCliente() ? 'Mis pedidos' : 'Inicio', 'icono' => 'inicio', 'roles' => ['administrador', 'emprendedor', 'cliente']],
        ['ruta' => 'admin.usuarios.index', 'activa' => 'admin.usuarios.*', 'texto' => 'Usuarios', 'icono' => 'usuarios', 'roles' => ['administrador']],
    ])->filter(fn ($item) => $usuario->tieneRol(...$item['roles']) && Route::has($item['ruta']));
@endphp
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-slate-800 antialiased">
    <a href="#contenido" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:rounded focus:bg-white focus:px-3 focus:py-2">Saltar al contenido</a>

    {{-- Fondo oscuro del menú en móvil --}}
    <div id="menu-fondo" class="fixed inset-0 z-30 hidden bg-slate-900/50 lg:hidden"></div>

    {{-- Menú lateral --}}
    <aside id="menu-lateral" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-marca-950 text-slate-300 transition-transform lg:translate-x-0">
        <div class="flex h-16 items-center gap-3 border-b border-white/10 px-5">
            <span class="flex size-9 items-center justify-center rounded-lg bg-marca-600 text-white">
                <x-icono nombre="producto" clase="size-5" />
            </span>
            <div class="leading-tight">
                <p class="text-sm font-bold tracking-wide text-white uppercase">Sistema de pedidos</p>
                <p class="text-xs text-slate-400">Emprendedores de Jutiapa</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto p-3" aria-label="Menú principal">
            @foreach ($menu as $item)
                @php $activa = request()->routeIs($item['activa']); @endphp
                <a href="{{ route($item['ruta']) }}" @if ($activa) aria-current="page" @endif
                   @class([
                       'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                       'bg-marca-600 text-white' => $activa,
                       'hover:bg-white/5 hover:text-white' => ! $activa,
                   ])>
                    <x-icono :nombre="$item['icono']" />
                    {{ $item['texto'] }}
                </a>
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium hover:bg-white/5 hover:text-white">
                    <x-icono nombre="salir" /> Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    <div class="flex min-h-full flex-col lg:pl-64">
        {{-- Encabezado --}}
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6">
            <button type="button" data-menu-toggle aria-controls="menu-lateral" aria-expanded="false" class="-ml-1 rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden">
                <x-icono nombre="menu" clase="size-6" />
                <span class="sr-only">Abrir menú</span>
            </button>

            <h1 class="truncate text-lg font-semibold text-slate-900">{{ $titulo }}</h1>

            <a href="{{ route('perfil.edit') }}" class="ml-auto flex items-center gap-3 rounded-lg px-2 py-1 hover:bg-slate-100">
                <span class="hidden text-right leading-tight sm:block">
                    <span class="block text-sm font-medium text-slate-800">{{ $usuario->nombre }}</span>
                    <span class="block text-xs text-slate-500">{{ $usuario->negocio ?: $usuario->rol->nombre }}</span>
                </span>
                <span class="flex size-9 items-center justify-center rounded-full bg-marca-100 text-sm font-semibold text-marca-700">{{ $usuario->iniciales() }}</span>
            </a>
        </header>

        <main id="contenido" class="flex-1 p-4 sm:p-6">
            <x-alertas />
            {{ $slot }}
        </main>

        <footer class="px-6 pb-4 text-center text-xs text-slate-400">
            © {{ date('Y') }} {{ config('app.name') }} · Universidad Mariano Gálvez, sede Jutiapa
        </footer>
    </div>
</body>
</html>
