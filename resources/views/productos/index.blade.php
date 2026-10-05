@php
    $pestanas = ['' => 'Todos', 'activos' => 'Activos', 'inactivos' => 'Inactivos'];
    $enlace = fn ($clave) => route('productos.index', array_filter(['buscar' => $buscar, 'estado' => $clave]));
@endphp
<x-layouts.app titulo="Productos">
    <x-slot:acciones>
        <button type="button" class="btn btn-secundario" data-abrir-dialogo="dialogo-catalogo"><x-icono nombre="compartir" clase="size-4" /> <span class="hidden sm:inline">Compartir catálogo</span><span class="sr-only sm:hidden">Compartir catálogo</span></button>
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
                           'flex min-h-10 items-center rounded-xl px-4 text-sm font-extrabold whitespace-nowrap relieve transition active:translate-y-px',
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
                <li class="relative">
                    <a href="{{ route('productos.edit', $producto) }}" class="group flex h-full flex-col overflow-hidden rounded-2xl bg-superficie shadow-suave transition hover:-translate-y-0.5 hover:shadow-flotante">
                        <span @class(['relative block aspect-[4/3] overflow-hidden', 'opacity-60 grayscale' => ! $producto->estado])>
                            <x-foto-producto :producto="$producto" class="transition duration-300 group-hover:scale-[1.03]" icono="size-12" />
                        </span>
                        <span class="flex flex-1 flex-col gap-0.5 p-3 sm:p-4">
                            <span class="flex items-start justify-between gap-2">
                                <span @class(['font-bold leading-snug', 'text-texto-2' => ! $producto->estado])>{{ $producto->nombre }}</span>
                            </span>
                            @if ($producto->descripcion)<span class="meta line-clamp-2">{{ $producto->descripcion }}</span>@endif
                            <x-moneda :valor="$producto->precio" :class="'mt-auto pt-2 text-[17px] font-extrabold '.($producto->estado ? 'text-texto' : 'text-texto-2')" />
                        </span>
                    </a>
                    {{-- Disponible / pausado con un toque, sin abrir el producto --}}
                    <form method="POST" action="{{ route('productos.disponible', $producto) }}" class="absolute top-2 right-2" data-envio-unico>
                        @csrf
                        @method('PATCH')
                        <button type="submit" role="switch" aria-checked="{{ $producto->estado ? 'true' : 'false' }}"
                                aria-label="{{ $producto->estado ? 'Pausar' : 'Activar' }} {{ $producto->nombre }}"
                                title="{{ $producto->estado ? 'Disponible: toque para pausarlo' : 'Pausado: toque para activarlo' }}"
                                class="flex cursor-pointer items-center gap-1.5 rounded-full bg-white/95 py-1 pr-2.5 pl-1 text-xs font-extrabold text-texto shadow-flotante backdrop-blur transition active:translate-y-px">
                            <span @class(['relative h-4 w-7 rounded-full transition-colors', 'bg-emerald-500' => $producto->estado, 'bg-stone-300' => ! $producto->estado])>
                                <span @class(['absolute top-0.5 size-3 rounded-full bg-white shadow transition-all', 'left-3.5' => $producto->estado, 'left-0.5' => ! $producto->estado])></span>
                            </span>
                            {{ $producto->estado ? 'Disponible' : 'Pausado' }}
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
        @if ($productos->hasPages())<div class="mt-5">{{ $productos->links() }}</div>@endif
    @endif

    {{-- Compartir el catálogo público con los clientes --}}
    @php
        $yo = auth()->user();
        $enlace = $yo->enlaceCatalogo();
        $compartir = 'https://wa.me/?text='.rawurlencode('Vea nuestros productos y haga su pedido aquí: '.$enlace);
    @endphp
    <dialog id="dialogo-catalogo" class="dialogo m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-borde bg-superficie p-0 text-texto shadow-flotante" aria-labelledby="catalogo-titulo">
        <div class="p-6">
            <h2 id="catalogo-titulo" class="titulo-seccion">Compartir su catálogo</h2>
            <p class="mt-1 text-sm text-texto-2">Sus clientes ven sus productos activos con foto y precio, eligen y le envían el pedido por WhatsApp. Usted lo registra en NEXO como siempre.</p>

            @unless ($yo->telefonoNegocio())
                <p class="mt-4 rounded-xl bg-oro-suave px-4 py-3 text-sm text-oro-texto">
                    Para que puedan pedirle por WhatsApp, <a href="{{ route('perfil.edit') }}" class="font-bold underline">agregue el WhatsApp de su negocio</a>.
                </p>
            @endunless

            <div class="mt-5 flex items-center gap-2 rounded-xl bg-superficie-2 p-2 pl-3">
                <span class="min-w-0 flex-1 truncate text-sm font-semibold select-all">{{ $enlace }}</span>
                <button type="button" class="btn btn-secundario btn-chico" data-copiar="{{ $enlace }}">Copiar</button>
            </div>

            <div class="mt-5 flex flex-col gap-2 sm:flex-row">
                <a href="{{ $compartir }}" target="_blank" rel="noopener" class="btn btn-whatsapp flex-1"><x-icono nombre="whatsapp" clase="size-5" /> Enviar por WhatsApp</a>
                <a href="{{ $enlace }}" target="_blank" rel="noopener" class="btn btn-secundario flex-1">Ver mi catálogo</a>
            </div>
            <button type="button" class="btn btn-terciario mt-3 w-full" data-cerrar-dialogo>Cerrar</button>
        </div>
    </dialog>
</x-layouts.app>
