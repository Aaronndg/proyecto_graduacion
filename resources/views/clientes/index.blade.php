<x-layouts.app titulo="Gestión de clientes">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <form method="GET" class="flex flex-1 gap-2" role="search">
            <div class="flex-1 sm:max-w-sm">
                <label for="buscar" class="sr-only">Buscar cliente</label>
                <input id="buscar" name="buscar" value="{{ $buscar }}" placeholder="Buscar por nombre, teléfono o correo" class="campo">
            </div>
            <button type="submit" class="btn btn-secundario"><x-icono nombre="buscar" clase="size-4" /> Buscar</button>
        </form>
        <a href="{{ route('clientes.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Nuevo cliente</a>
    </div>

    <div class="tarjeta overflow-hidden">
        @if ($clientes->isEmpty())
            <x-vacio icono="usuarios" :titulo="$buscar ? 'No se encontraron clientes' : 'Aún no tiene clientes registrados'"
                     :texto="$buscar ? 'Pruebe con otro nombre, teléfono o correo.' : 'Registre a sus clientes para asociarlos con sus pedidos.'">
                @unless ($buscar)<a href="{{ route('clientes.create') }}" class="btn btn-primario">Registrar el primer cliente</a>@endunless
            </x-vacio>
        @else
            <div class="overflow-x-auto">
                <table class="tabla">
                    <thead>
                        <tr><th>Nombre</th><th>Teléfono</th><th class="hidden md:table-cell">Correo</th><th class="text-center">Pedidos</th><th class="text-right">Acciones</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($clientes as $cliente)
                            <tr>
                                <td class="font-medium text-slate-900">
                                    <a href="{{ route('clientes.show', $cliente) }}" class="hover:text-marca-600 hover:underline">{{ $cliente->nombre }}</a>
                                </td>
                                <td class="whitespace-nowrap">{{ $cliente->telefono ?? '—' }}</td>
                                <td class="hidden md:table-cell">{{ $cliente->correo ?? '—' }}</td>
                                <td class="text-center tabular-nums">{{ $cliente->pedidos_count }}</td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-secundario px-3 py-1.5"><x-icono nombre="editar" clase="size-4" /> Editar</a>
                                        <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-secundario px-3 py-1.5">Ver</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">{{ $clientes->links() }}</div>
</x-layouts.app>
