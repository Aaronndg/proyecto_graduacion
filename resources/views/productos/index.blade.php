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
        <div class="panel flex flex-col items-center px-5 py-10 text-center">
            <x-ilustracion nombre="caja" class="mb-3" />
            @if ($buscar || $estado)
                <p class="font-bold">No encontramos productos{{ $buscar ? ' con «'.$buscar.'»' : '' }}</p>
                <a href="{{ route('productos.index') }}" class="btn btn-secundario mt-5">Ver todos los productos</a>
            @else
                <p class="font-bold">Aún no tiene productos</p>
                <p class="mt-1 text-sm text-texto-2">Agregue lo que vende, con su foto, para poder registrar pedidos.</p>
                <a href="{{ route('productos.create') }}" class="btn btn-primario mt-5"><x-icono nombre="mas" clase="size-4" /> Agregar producto</a>
            @endif
        </div>
    @else
        {{-- Catálogo: cada producto es una tarjeta con su foto que abre la edición --}}
        <ul class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4">
            @foreach ($productos as $producto)
                <li>
                    <a href="{{ route('productos.edit', $producto) }}" class="group flex h-full flex-col overflow-hidden rounded-2xl bg-superficie shadow-suave transition hover:-translate-y-0.5 hover:shadow-flotante">
                        <span @class(['relative block aspect-[4/3] overflow-hidden', 'opacity-60 grayscale' => ! $producto->estado])>
                            <x-foto-producto :producto="$producto" class="transition duration-300 group-hover:scale-[1.03]" icono="size-12" />
                        </span>
                        <span class="flex flex-1 flex-col gap-0.5 p-3 sm:p-4">
                            <span class="flex items-start justify-between gap-2">
                                <span @class(['font-bold leading-snug', 'text-texto-2' => ! $producto->estado])>{{ $producto->nombre }}</span>
                                @unless ($producto->estado)<span class="insignia shrink-0 bg-superficie-2 text-texto-2">Inactivo</span>@endunless
                            </span>
                            @if ($producto->descripcion)<span class="meta line-clamp-2">{{ $producto->descripcion }}</span>@endif
                            <x-moneda :valor="$producto->precio" :class="'mt-auto pt-2 text-[17px] font-extrabold '.($producto->estado ? 'text-texto' : 'text-texto-2')" />
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
        @if ($productos->hasPages())<div class="mt-5">{{ $productos->links() }}</div>@endif
    @endif
</x-layouts.app>
