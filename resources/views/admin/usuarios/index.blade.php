@php
    use App\Models\Rol;

    $pestanas = ['' => 'Todos', Rol::EMPRENDEDOR => 'Emprendedores', Rol::CLIENTE => 'Clientes', Rol::ADMINISTRADOR => 'Administradores'];
    $enlace = fn ($clave) => route('admin.usuarios.index', array_filter(['buscar' => $buscar, 'rol' => $clave]));
@endphp
<x-layouts.app titulo="Usuarios">
    <x-slot:acciones>
        <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> <span>Nuevo<span class="hidden sm:inline"> usuario</span></span></a>
    </x-slot:acciones>

    <form method="GET" class="mb-4 flex gap-2" role="search">
        @if ($rol)<input type="hidden" name="rol" value="{{ $rol }}">@endif
        <x-campo-busqueda :valor="$buscar" placeholder="Nombre, correo o negocio" />
        <button type="submit" class="btn btn-secundario">Buscar</button>
    </form>

    <nav class="-mx-4 mb-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Usuarios por rol" data-desplazable>
        <ul class="flex w-max gap-2 pb-1 sm:w-auto sm:flex-wrap">
            @foreach ($pestanas as $clave => $texto)
                @php $actual = (string) $rol === (string) $clave; @endphp
                <li>
                    <a href="{{ $enlace($clave) }}" @if ($actual) aria-current="page" @endif
                       @class([
                           'flex min-h-10 items-center rounded-xl px-4 text-sm font-extrabold whitespace-nowrap shadow-suave transition-colors',
                           'bg-marca text-white' => $actual,
                           'bg-superficie text-texto-2 hover:text-marca' => ! $actual,
                       ])>{{ $texto }}</a>
                </li>
            @endforeach
        </ul>
    </nav>

    @if ($usuarios->isEmpty())
        <div class="panel px-5 py-10 text-center">
            <p class="font-medium">No encontramos usuarios{{ $buscar ? ' con «'.$buscar.'»' : '' }}</p>
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secundario mt-5">Ver todos los usuarios</a>
        </div>
    @else
        <ul class="panel overflow-hidden">
            @foreach ($usuarios as $usuario)
                <li class="border-b border-borde last:border-b-0">
                    <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-superficie-2/60">
                        <x-avatar :nombre="$usuario->nombre" :class="$usuario->activo ? '' : 'opacity-50'" />
                        <span class="min-w-0 flex-1">
                            <span @class(['block truncate font-medium', 'text-texto-2' => ! $usuario->activo])>
                                {{ $usuario->nombre }}
                                @if ($usuario->is(auth()->user()))<span class="meta font-normal">(usted)</span>@endif
                            </span>
                            <span class="meta block truncate">{{ collect([$usuario->activo ? null : 'Desactivada', $usuario->correo])->filter()->implode(' · ') }}</span>
                        </span>
                        <span class="shrink-0 text-right text-sm">
                            <span @class(['block', 'text-texto-2' => ! $usuario->activo])>{{ $usuario->rol->nombre }}</span>
                            @if ($usuario->negocio)<span class="meta block max-w-40 truncate">{{ $usuario->negocio }}</span>@endif
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
        @if ($usuarios->hasPages())<div class="mt-4">{{ $usuarios->links() }}</div>@endif
    @endif
</x-layouts.app>
