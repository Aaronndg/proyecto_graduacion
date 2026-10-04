@php $primerNombre = strtok($cliente->nombre, ' '); @endphp
<x-layouts.app :titulo="$cliente->nombre" :subtitulo="'Cliente desde '.$cliente->created_at?->translatedFormat('F \d\e Y')"
                :ruta="['Clientes' => route('clientes.index')]">
    <x-slot:acciones>
        <a href="{{ route('pedidos.create', ['cliente' => $cliente->id_cliente]) }}" class="btn btn-primario hidden sm:inline-flex"><x-icono nombre="mas" clase="size-4" /> Nuevo pedido</a>
        <x-menu-acciones etiqueta="Más acciones del cliente">
            <a href="{{ route('clientes.edit', $cliente) }}" class="menu-opcion"><x-icono nombre="editar" clase="size-4 text-texto-2" /> Editar cliente</a>
            @if ($pedidos->total() === 0)
                <form method="POST" action="{{ route('clientes.destroy', $cliente) }}"
                      data-confirmar-titulo="¿Eliminar a {{ $cliente->nombre }}?" data-confirmar="Se borrarán sus datos de contacto. Esta acción no se puede deshacer."
                      data-confirmar-accion="Eliminar cliente" data-confirmar-peligro>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="menu-opcion menu-opcion-peligro"><x-icono nombre="cerrar" clase="size-4" /> Eliminar cliente</button>
                </form>
            @endif
        </x-menu-acciones>
    </x-slot:acciones>

    {{-- Teléfono: la acción principal a todo el ancho, al alcance del pulgar --}}
    <a href="{{ route('pedidos.create', ['cliente' => $cliente->id_cliente]) }}" class="btn btn-primario mb-6 w-full sm:hidden"><x-icono nombre="mas" clase="size-4" /> Nuevo pedido</a>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_21rem] lg:items-start">
        {{-- Sus pedidos: lo más consultado --}}
        <section class="panel overflow-hidden" aria-labelledby="titulo-pedidos">
            <h2 id="titulo-pedidos" class="titulo-seccion px-5 pt-4 pb-3">
                Pedidos @if ($pedidos->total())<span class="font-normal text-texto-2">· {{ $pedidos->total() }}</span>@endif
            </h2>

            @if ($pedidos->isEmpty())
                <div class="border-t border-borde px-5 py-8 text-center">
                    <p class="font-medium">{{ $primerNombre }} aún no tiene pedidos</p>
                    <a href="{{ route('pedidos.create', ['cliente' => $cliente->id_cliente]) }}" class="btn btn-secundario mt-4">Registrar su primer pedido</a>
                </div>
            @else
                <ul class="border-t border-borde">
                    @foreach ($pedidos as $pedido)
                        <li class="border-b border-borde last:border-b-0">
                            <a href="{{ route('pedidos.show', $pedido) }}"
                               class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-1 px-5 py-3 transition-colors hover:bg-superficie-2/60 md:grid-cols-[4.5rem_minmax(0,1fr)_8.5rem_7.5rem]">
                                <span class="font-medium text-marca tabular-nums">#{{ $pedido->numero() }}
                                    <span class="meta font-normal md:hidden">· {{ $pedido->fecha->format('d/m/Y') }}</span>
                                </span>
                                <span class="hidden text-sm text-texto-2 tabular-nums md:block">{{ $pedido->fecha->format('d/m/Y') }}</span>
                                <span class="col-start-1 row-start-2 md:col-start-3 md:row-start-1"><x-estado-pedido :estado="$pedido->estado" /></span>
                                <x-moneda :valor="$pedido->total" class="col-start-2 row-start-1 text-right font-medium md:col-start-4" />
                            </a>
                        </li>
                    @endforeach
                </ul>
                @if ($pedidos->hasPages())<div class="border-t border-borde px-5 py-3">{{ $pedidos->links() }}</div>@endif
            @endif
        </section>

        <div class="space-y-6">
            <section class="panel p-5" aria-labelledby="titulo-contacto">
                <h2 id="titulo-contacto" class="titulo-seccion">Contacto</h2>
                <dl class="mt-3 space-y-3 text-sm">
                    @foreach (['Teléfono' => $cliente->telefono, 'Correo' => $cliente->correo, 'Dirección' => $cliente->direccion] as $dato => $valor)
                        <div>
                            <dt class="text-texto-2">{{ $dato }}</dt>
                            <dd @class(['break-words', 'text-texto-2' => ! $valor])>{{ $valor ?? 'Sin '.mb_strtolower($dato) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <x-invitacion-cliente :cliente="$cliente" />
        </div>
    </div>
</x-layouts.app>
