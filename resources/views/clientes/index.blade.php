@php
    $ultimo = fn ($cliente) => $cliente->ultimo_pedido
        ? 'último '.\Illuminate\Support\Carbon::parse($cliente->ultimo_pedido)->diffForHumans()
        : null;
@endphp
<x-layouts.app titulo="Clientes">
    <x-slot:acciones>
        <a href="{{ route('clientes.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> <span>Nuevo<span class="hidden sm:inline"> cliente</span></span></a>
    </x-slot:acciones>

    <form method="GET" class="mb-4 flex gap-2" role="search">
        <x-campo-busqueda :valor="$buscar" placeholder="Nombre, teléfono o correo" />
        <button type="submit" class="btn btn-secundario">Buscar</button>
    </form>

    @if ($clientes->isEmpty())
        <div class="panel flex flex-col items-center px-5 py-10 text-center">
            <x-ilustracion nombre="clientes" class="mb-3" />
            @if ($buscar)
                <p class="font-medium">No encontramos clientes con «{{ $buscar }}»</p>
                <p class="mt-1 text-sm text-texto-2">Pruebe con otro nombre, teléfono o correo.</p>
                <a href="{{ route('clientes.index') }}" class="btn btn-secundario mt-5">Quitar búsqueda</a>
            @else
                <p class="font-medium">Aún no tiene clientes</p>
                <p class="mt-1 text-sm text-texto-2">Regístrelos aquí o al crear un pedido.</p>
                <a href="{{ route('clientes.create') }}" class="btn btn-primario mt-5"><x-icono nombre="mas" clase="size-4" /> Registrar cliente</a>
            @endif
        </div>
    @else
        <div class="panel overflow-hidden">
            <div class="hidden grid-cols-[minmax(0,1fr)_8rem_10rem_5rem] gap-4 border-b border-borde bg-superficie-2 px-4 py-2.5 text-[13px] font-medium text-texto-2 md:grid" aria-hidden="true">
                <span>Cliente</span><span>Teléfono</span><span>Último pedido</span><span class="text-right">Pedidos</span>
            </div>

            <ul>
                @foreach ($clientes as $cliente)
                    @php $conteo = $cliente->pedidos_count.' '.($cliente->pedidos_count === 1 ? 'pedido' : 'pedidos'); @endphp
                    <li class="border-b border-borde last:border-b-0">
                        <a href="{{ route('clientes.show', $cliente) }}"
                           class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-0.5 px-4 py-3 transition-colors hover:bg-superficie-2/60 md:grid-cols-[minmax(0,1fr)_8rem_10rem_5rem]">
                            <span class="flex min-w-0 items-center gap-3">
                                <x-avatar :nombre="$cliente->nombre" />
                                <span class="min-w-0">
                                    <span class="block truncate font-medium">{{ $cliente->nombre }}</span>
                                    <span class="meta hidden truncate md:block">
                                        {{ collect([$cliente->correo, $cliente->id_usuario ? 'Ve sus pedidos en línea' : null])->filter()->implode(' · ') ?: ' ' }}
                                    </span>
                                </span>
                            </span>
                            <span class="meta col-span-2 row-start-2 truncate pl-13 md:hidden">
                                {{ collect([$cliente->telefono, $ultimo($cliente), $cliente->id_usuario ? 'en línea' : null])->filter()->implode(' · ') ?: 'Sin teléfono' }}
                            </span>
                            <span class="hidden text-sm text-texto-2 tabular-nums md:block">{{ $cliente->telefono ?? '—' }}</span>
                            <span class="hidden text-sm text-texto-2 md:block">
                                {{ $cliente->ultimo_pedido ? \Illuminate\Support\Carbon::parse($cliente->ultimo_pedido)->diffForHumans() : 'Sin pedidos' }}
                            </span>
                            <span class="col-start-2 row-start-1 text-right text-sm tabular-nums md:col-start-4">
                                <span class="md:hidden">{{ $conteo }}</span><span class="hidden md:inline">{{ $cliente->pedidos_count }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>

            @if ($clientes->hasPages())<div class="border-t border-borde px-4 py-3">{{ $clientes->links() }}</div>@endif
        </div>
    @endif
</x-layouts.app>
