<x-layouts.app :titulo="$cliente->nombre">
    <a href="{{ route('clientes.index') }}" class="mb-4 inline-block text-sm font-medium text-marca-600 hover:underline">&larr; Volver a clientes</a>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="tarjeta p-6 lg:col-span-1">
            <div class="flex items-center gap-3">
                <span class="flex size-12 items-center justify-center rounded-full bg-marca-100 text-lg font-semibold text-marca-700">
                    {{ mb_strtoupper(mb_substr($cliente->nombre, 0, 1)) }}
                </span>
                <div>
                    <h2 class="font-semibold text-slate-900">{{ $cliente->nombre }}</h2>
                    <p class="text-xs text-slate-500">Cliente desde {{ $cliente->created_at?->format('d/m/Y') }}</p>
                </div>
            </div>

            <dl class="mt-6 space-y-3 text-sm">
                <div><dt class="text-slate-500">Teléfono</dt><dd class="font-medium text-slate-800">{{ $cliente->telefono ?? 'No registrado' }}</dd></div>
                <div><dt class="text-slate-500">Correo</dt><dd class="font-medium break-all text-slate-800">{{ $cliente->correo ?? 'No registrado' }}</dd></div>
                <div><dt class="text-slate-500">Dirección</dt><dd class="font-medium text-slate-800">{{ $cliente->direccion ?? 'No registrada' }}</dd></div>
                <div>
                    <dt class="text-slate-500">Seguimiento en línea</dt>
                    <dd class="font-medium text-slate-800">
                        {{ $cliente->usuario ? 'Tiene cuenta y puede consultar sus pedidos' : 'Sin cuenta vinculada' }}
                    </dd>
                </div>
            </dl>

            <div class="mt-6 flex flex-wrap gap-2 border-t border-slate-200 pt-5">
                <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-secundario"><x-icono nombre="editar" clase="size-4" /> Editar</a>
                @if ($pedidos->total() === 0)
                    <form method="POST" action="{{ route('clientes.destroy', $cliente) }}" data-confirmar="¿Eliminar a {{ $cliente->nombre }}? Esta acción no se puede deshacer.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-peligro">Eliminar</button>
                    </form>
                @endif
            </div>
        </section>

        <section class="tarjeta lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Historial de pedidos</h2>
                <a href="{{ route('pedidos.create', ['cliente' => $cliente->id_cliente]) }}" class="btn btn-primario px-3 py-1.5"><x-icono nombre="mas" clase="size-4" /> Nuevo pedido</a>
            </div>

            @if ($pedidos->isEmpty())
                <x-vacio titulo="Este cliente aún no tiene pedidos" />
            @else
                <div class="overflow-x-auto">
                    <table class="tabla">
                        <thead><tr><th>No.</th><th>Fecha</th><th>Estado</th><th class="text-right">Total</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($pedidos as $pedido)
                                <tr>
                                    <td><a href="{{ route('pedidos.show', $pedido) }}" class="font-medium text-marca-600 hover:underline">#{{ $pedido->numero() }}</a></td>
                                    <td class="whitespace-nowrap">{{ $pedido->fecha->format('d/m/Y') }}</td>
                                    <td><x-estado-pedido :estado="$pedido->estado" /></td>
                                    <td class="text-right"><x-moneda :valor="$pedido->total" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-3">{{ $pedidos->links() }}</div>
            @endif
        </section>
    </div>
</x-layouts.app>
