<x-layouts.app titulo="Gestión de pedidos">
    <div class="mb-4 flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
        <form method="GET" class="grid flex-1 gap-2 sm:grid-cols-2 lg:grid-cols-5 lg:items-end" role="search">
            <div class="lg:col-span-2">
                <label for="buscar" class="etiqueta">Buscar</label>
                <input id="buscar" name="buscar" value="{{ $filtros['buscar'] }}" placeholder="Número de pedido o cliente" class="campo">
            </div>
            <div>
                <label for="estado" class="etiqueta">Estado</label>
                <select id="estado" name="estado" class="campo">
                    <option value="">Todos</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->id_estado }}" @selected($filtros['estado'] === $estado->id_estado)>{{ $estado->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="desde" class="etiqueta">Desde</label>
                <input id="desde" name="desde" type="date" value="{{ $filtros['desde'] }}" class="campo">
            </div>
            <div>
                <label for="hasta" class="etiqueta">Hasta</label>
                <input id="hasta" name="hasta" type="date" value="{{ $filtros['hasta'] }}" class="campo">
            </div>
            <div class="flex gap-2 sm:col-span-2 lg:col-span-5">
                <button type="submit" class="btn btn-secundario"><x-icono nombre="buscar" clase="size-4" /> Consultar</button>
                @if (array_filter($filtros))
                    <a href="{{ route('pedidos.index') }}" class="btn px-3 text-slate-600 hover:text-slate-900">Limpiar filtros</a>
                @endif
            </div>
        </form>
        <a href="{{ route('pedidos.create') }}" class="btn btn-primario self-start xl:self-end"><x-icono nombre="mas" clase="size-4" /> Nuevo pedido</a>
    </div>

    <div class="tarjeta overflow-hidden">
        @if ($pedidos->isEmpty())
            <x-vacio :titulo="array_filter($filtros) ? 'No se encontraron pedidos con esos criterios' : 'Aún no hay pedidos registrados'">
                @unless (array_filter($filtros))<a href="{{ route('pedidos.create') }}" class="btn btn-primario">Registrar el primer pedido</a>@endunless
            </x-vacio>
        @else
            <div class="overflow-x-auto">
                <table class="tabla">
                    <thead>
                        <tr><th>No.</th><th>Cliente</th><th>Fecha</th><th>Estado</th><th class="text-right">Total</th><th class="text-right">Acciones</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($pedidos as $pedido)
                            <tr>
                                <td><a href="{{ route('pedidos.show', $pedido) }}" class="font-medium text-marca-600 hover:underline">#{{ $pedido->numero() }}</a></td>
                                <td class="font-medium text-slate-900">{{ $pedido->cliente->nombre }}</td>
                                <td class="whitespace-nowrap">{{ $pedido->fecha->format('d/m/Y H:i') }}</td>
                                <td><x-estado-pedido :estado="$pedido->estado" /></td>
                                <td class="text-right"><x-moneda :valor="$pedido->total" /></td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('pedidos.show', $pedido) }}" class="btn btn-secundario px-3 py-1.5">Ver</a>
                                        @unless (in_array($pedido->id_estado, \App\Models\EstadoPedido::FINALES, true))
                                            <a href="{{ route('pedidos.edit', $pedido) }}" class="btn btn-secundario px-3 py-1.5"><x-icono nombre="editar" clase="size-4" /> Editar</a>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">{{ $pedidos->links() }}</div>
</x-layouts.app>
