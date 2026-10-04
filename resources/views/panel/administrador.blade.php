@php
    use App\Models\Rol;

    $plural = fn (int $n, string $uno, string $varios) => number_format($n).' '.($n === 1 ? $uno : $varios);
@endphp
<x-layouts.app :titulo="'Hola, '.strtok(auth()->user()->nombre, ' ')">
    <x-slot:acciones>
        <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> <span>Nuevo<span class="hidden sm:inline"> usuario</span></span></a>
    </x-slot:acciones>

    {{-- Resumen de la plataforma en una frase --}}
    <section class="mb-8" aria-labelledby="titulo-resumen">
        <h2 id="titulo-resumen" class="meta mb-1">En la plataforma</h2>
        <p class="text-xl font-semibold tracking-tight text-balance sm:text-2xl">
            <a href="{{ route('admin.usuarios.index', ['rol' => Rol::EMPRENDEDOR]) }}" class="hover:underline">{{ $plural($totalEmprendedores, 'emprendedor', 'emprendedores') }}</a>
            <span class="text-texto-2">·</span>
            <a href="{{ route('admin.usuarios.index', ['rol' => Rol::CLIENTE]) }}" class="hover:underline">{{ $plural($totalClientes, 'cliente con cuenta', 'clientes con cuenta') }}</a>
        </p>
        <p class="mt-1 text-sm text-texto-2">
            {{ $plural($totalPedidos, 'pedido registrado', 'pedidos registrados') }}
            @if ($totalInactivos)
                · <a href="{{ route('admin.usuarios.index') }}" class="enlace">{{ $plural($totalInactivos, 'cuenta desactivada', 'cuentas desactivadas') }}</a>
            @endif
        </p>
    </section>

    <section class="panel overflow-hidden" aria-labelledby="titulo-recientes">
        <div class="flex items-center justify-between px-5 pt-4 pb-3">
            <h2 id="titulo-recientes" class="titulo-seccion">Usuarios recientes</h2>
            <a href="{{ route('admin.usuarios.index') }}" class="enlace text-sm">Ver todos</a>
        </div>
        <ul class="border-t border-borde">
            @foreach ($usuariosRecientes as $usuario)
                <li class="border-b border-borde last:border-b-0">
                    <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="flex items-center justify-between gap-4 px-5 py-3 transition-colors hover:bg-superficie-2/60">
                        <span class="min-w-0">
                            <span class="block truncate font-medium">{{ $usuario->nombre }}</span>
                            <span class="meta block truncate">{{ $usuario->correo }}</span>
                        </span>
                        <span class="shrink-0 text-right text-sm">
                            <span class="block">{{ $usuario->rol->nombre }}</span>
                            <span class="meta block">{{ $usuario->created_at?->translatedFormat('j M Y') }}</span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
</x-layouts.app>
