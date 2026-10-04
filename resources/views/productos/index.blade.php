@php
    $pestanas = ['' => 'Todos', 'activos' => 'Activos', 'inactivos' => 'Inactivos'];
    $enlace = fn ($clave) => route('productos.index', array_filter(['buscar' => $buscar, 'estado' => $clave]));
@endphp
<x-layouts.app titulo="Productos">
    <x-slot:acciones>
        <a href="{{ route('productos.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> <span>Nuevo<span class="hidden sm:inline"> producto</span></span></a>
    </x-slot:acciones>

    <form method="GET" class="mb-4 flex gap-2" role="search">
        @if ($estado)<input type="hidden" name="estado" value="{{ $estado }}">@endif
        <x-campo-busqueda :valor="$buscar" placeholder="Nombre del producto" />
        <button type="submit" class="btn btn-secundario">Buscar</button>
    </form>

    <nav class="mb-4" aria-label="Productos por disponibilidad">
        <ul class="flex flex-wrap gap-2 pb-1">
            @foreach ($pestanas as $clave => $texto)
                @php $actual = (string) $estado === (string) $clave; @endphp
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

    @if ($productos->isEmpty())
        <div class="panel px-5 py-10 text-center">
            @if ($buscar || $estado)
                <p class="font-medium">No encontramos productos{{ $buscar ? ' con «'.$buscar.'»' : '' }}</p>
                <a href="{{ route('productos.index') }}" class="btn btn-secundario mt-5">Ver todos los productos</a>
            @else
                <p class="font-medium">Aún no tiene productos</p>
                <p class="mt-1 text-sm text-texto-2">Agregue lo que vende para poder registrar pedidos.</p>
                <a href="{{ route('productos.create') }}" class="btn btn-primario mt-5"><x-icono nombre="mas" clase="size-4" /> Agregar producto</a>
            @endif
        </div>
    @else
        <ul class="panel overflow-hidden">
            @foreach ($productos as $producto)
                <li class="border-b border-borde last:border-b-0">
                    <a href="{{ route('productos.edit', $producto) }}" class="flex items-center justify-between gap-4 px-4 py-3 transition-colors hover:bg-superficie-2/60">
                        <span class="min-w-0">
                            <span @class(['block truncate font-medium', 'text-texto-2' => ! $producto->estado])>{{ $producto->nombre }}</span>
                            @if (! $producto->estado || $producto->descripcion)
                                <span class="meta block truncate">
                                    {{ collect([$producto->estado ? null : 'Inactivo', $producto->descripcion])->filter()->implode(' · ') }}
                                </span>
                            @endif
                        </span>
                        <x-moneda :valor="$producto->precio" :class="$producto->estado ? 'shrink-0 font-medium' : 'shrink-0 font-medium text-texto-2'" />
                    </a>
                </li>
            @endforeach
        </ul>
        @if ($productos->hasPages())<div class="mt-4">{{ $productos->links() }}</div>@endif
    @endif
</x-layouts.app>
