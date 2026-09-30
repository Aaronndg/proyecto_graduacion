<x-layouts.app titulo="Clientes" subtitulo="Las personas que le compran.">
    <x-slot:acciones>
        <a href="{{ route('clientes.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Nuevo cliente</a>
    </x-slot:acciones>

    <div class="tarjeta overflow-hidden">
        <form method="GET" class="flex gap-2 border-b border-stone-100 p-4" role="search">
            <x-campo-busqueda :valor="$buscar" placeholder="Buscar por nombre, teléfono o correo" />
            <button type="submit" class="btn btn-secundario">Buscar</button>
        </form>

        @if ($clientes->isEmpty())
            <x-vacio icono="usuarios" :titulo="$buscar ? 'No encontramos clientes' : 'Aún no tiene clientes'"
                     :texto="$buscar ? 'Pruebe con otro nombre, teléfono o correo.' : 'Registre a sus clientes para asociarlos con sus pedidos.'">
                @unless ($buscar)<a href="{{ route('clientes.create') }}" class="btn btn-primario">Registrar el primer cliente</a>@endunless
            </x-vacio>
        @else
            <div class="overflow-x-auto">
                <table class="tabla">
                    <thead>
                        <tr><th>Cliente</th><th>Teléfono</th><th class="hidden md:table-cell">Seguimiento en línea</th><th class="text-center">Pedidos</th><th><span class="sr-only">Acciones</span></th></tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($clientes as $cliente)
                            <tr>
                                <td>
                                    <a href="{{ route('clientes.show', $cliente) }}" class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-marca-50 text-sm font-semibold text-marca-700">{{ mb_strtoupper(mb_substr($cliente->nombre, 0, 1)) }}</span>
                                        <span class="min-w-0">
                                            <span class="block font-medium text-stone-900 hover:text-marca-700">{{ $cliente->nombre }}</span>
                                            @if ($cliente->correo)<span class="block truncate text-xs text-stone-500">{{ $cliente->correo }}</span>@endif
                                        </span>
                                    </a>
                                </td>
                                <td class="whitespace-nowrap text-stone-600">{{ $cliente->telefono ?? '—' }}</td>
                                <td class="hidden md:table-cell">
                                    @if ($cliente->id_usuario)
                                        <span class="insignia bg-emerald-50 text-emerald-700 ring-emerald-600/20">Conectado</span>
                                    @else
                                        <span class="text-xs text-stone-400">Sin invitar</span>
                                    @endif
                                </td>
                                <td class="text-center font-medium tabular-nums">{{ $cliente->pedidos_count }}</td>
                                <td class="text-right">
                                    <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-secundario px-3 py-1.5">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($clientes->hasPages())<div class="border-t border-stone-100 px-5 py-3">{{ $clientes->links() }}</div>@endif
        @endif
    </div>
</x-layouts.app>
