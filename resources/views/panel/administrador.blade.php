@php
    use App\Models\Rol;

    $plural = fn (int $n, string $uno, string $varios) => number_format($n).' '.($n === 1 ? $uno : $varios);
@endphp
<x-layouts.app titulo="Inicio" :encabezado="false">
    {{-- Resumen de la plataforma en el banner de bienvenida --}}
    <x-bienvenida :titulo="'¡Hola, '.strtok(auth()->user()->nombre, ' ').'!'" antetitulo="En la plataforma">
        <a href="{{ route('admin.usuarios.index', ['rol' => Rol::EMPRENDEDOR]) }}" class="font-extrabold text-white hover:underline">{{ $plural($totalEmprendedores, 'emprendedor', 'emprendedores') }}</a>
        ·
        <a href="{{ route('admin.usuarios.index', ['rol' => Rol::CLIENTE]) }}" class="font-extrabold text-white hover:underline">{{ $plural($totalClientes, 'cliente con cuenta', 'clientes con cuenta') }}</a>
        · {{ $plural($totalPedidos, 'pedido registrado', 'pedidos registrados') }}
        @if ($totalInactivos)
            · <a href="{{ route('admin.usuarios.index') }}" class="font-extrabold text-[#E9C46A] hover:underline">{{ $plural($totalInactivos, 'cuenta desactivada', 'cuentas desactivadas') }}</a>
        @endif
        <x-slot:acciones>
            <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Nuevo usuario</a>
            <a href="{{ route('admin.usuarios.index') }}" class="btn border-2 border-white/70 text-white hover:bg-white/10">Ver todos los usuarios</a>
        </x-slot:acciones>
    </x-bienvenida>

    {{-- Accesos: íconos en círculos hacia la lista ya filtrada --}}
    <nav class="mb-8 grid grid-cols-3 gap-2 sm:gap-4" aria-label="Accesos rápidos">
        @foreach ([
            ['Emprendedores', 'negocio', Rol::EMPRENDEDOR, 'bg-oro-suave'],
            ['Clientes', 'clientes', Rol::CLIENTE, 'bg-marca-100'],
            ['Administradores', 'usuario', Rol::ADMINISTRADOR, 'bg-oro-suave'],
        ] as [$texto, $icono, $idRol, $tono])
            <a href="{{ route('admin.usuarios.index', ['rol' => $idRol]) }}" class="flex flex-col items-center gap-2 rounded-2xl p-1 text-center transition sm:flex-row sm:gap-3 sm:bg-superficie sm:p-4 sm:text-left sm:shadow-suave sm:hover:-translate-y-0.5 sm:hover:shadow-flotante">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-full shadow-suave sm:size-13 sm:shadow-none {{ $tono }}"><x-icono-duo :nombre="$icono" /></span>
                <span class="text-[13px] font-bold sm:text-base">{{ $texto }}</span>
            </a>
        @endforeach
    </nav>

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
