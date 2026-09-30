<x-layouts.app titulo="Pedidos" subtitulo="Todos los pedidos de su negocio.">
    <x-slot:acciones>
        <a href="{{ route('pedidos.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Nuevo pedido</a>
    </x-slot:acciones>

    <div class="tarjeta overflow-hidden">
        <form method="GET" class="grid gap-2 border-b border-stone-100 p-4 sm:grid-cols-2 lg:grid-cols-[1fr_11rem_9.5rem_9.5rem_auto]" role="search">
            <x-campo-busqueda :valor="$filtros['buscar']" placeholder="Número de pedido o cliente" />
            <div>
                <label for="estado" class="sr-only">Estado</label>
                <select id="estado" name="estado" class="campo">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->id_estado }}" @selected($filtros['estado'] === $estado->id_estado)>{{ $estado->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="desde" class="sr-only">Desde</label>
                <input id="desde" name="desde" type="date" value="{{ $filtros['desde'] }}" class="campo" title="Desde">
            </div>
            <div>
                <label for="hasta" class="sr-only">Hasta</label>
                <input id="hasta" name="hasta" type="date" value="{{ $filtros['hasta'] }}" class="campo" title="Hasta">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-secundario flex-1">Buscar</button>
                @if (array_filter($filtros))
                    <a href="{{ route('pedidos.index') }}" class="btn px-3 text-stone-500 hover:text-stone-900" title="Limpiar filtros">&times;</a>
                @endif
            </div>
        </form>

        @if ($pedidos->isEmpty())
            <x-vacio :titulo="array_filter($filtros) ? 'No encontramos pedidos con esos datos' : 'Aún no hay pedidos'">
                @unless (array_filter($filtros))<a href="{{ route('pedidos.create') }}" class="btn btn-primario">Registrar el primer pedido</a>@endunless
            </x-vacio>
        @else
            <div class="overflow-x-auto">
                <table class="tabla">
                    <thead>
                        <tr><th>Pedido</th><th>Cliente</th><th>Fecha</th><th>Estado</th><th class="text-right">Total</th><th><span class="sr-only">Acciones</span></th></tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($pedidos as $pedido)
                            <tr>
                                <td><a href="{{ route('pedidos.show', $pedido) }}" class="enlace">#{{ $pedido->numero() }}</a></td>
                                <td class="font-medium text-stone-900">{{ $pedido->cliente->nombre }}</td>
                                <td class="whitespace-nowrap text-stone-500">{{ $pedido->fecha->format('d/m/Y H:i') }}</td>
                                <td><x-estado-pedido :estado="$pedido->estado" /></td>
                                <td class="text-right font-medium"><x-moneda :valor="$pedido->total" /></td>
                                <td class="text-right">
                                    <a href="{{ route('pedidos.show', $pedido) }}" class="btn btn-secundario px-3 py-1.5">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($pedidos->hasPages())<div class="border-t border-stone-100 px-5 py-3">{{ $pedidos->links() }}</div>@endif
        @endif
    </div>
</x-layouts.app>
