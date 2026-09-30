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
            </dl>

            {{-- Seguimiento en línea: vinculación de la cuenta del cliente con el código (RN-06) --}}
            <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-4">
                <h3 class="text-sm font-semibold text-slate-900">Seguimiento en línea</h3>

                @if ($cliente->usuario)
                    <p class="mt-1 flex items-center gap-1.5 text-sm text-emerald-700">
                        <x-icono nombre="ok" clase="size-4" /> Vinculado con la cuenta {{ $cliente->usuario->correo }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">El cliente puede consultar el estado de sus pedidos.</p>
                    <form method="POST" action="{{ route('clientes.codigo', $cliente) }}" class="mt-3"
                          data-confirmar="¿Desvincular la cuenta {{ $cliente->usuario->correo }}? Dejará de ver sus pedidos hasta que use un código nuevo.">
                        @csrf
                        <button type="submit" class="text-xs font-medium text-red-600 hover:underline">Desvincular y generar código nuevo</button>
                    </form>
                @else
                    <p class="mt-1 text-xs text-slate-500">Entregue este código al cliente para que vea sus pedidos al crear su cuenta.</p>
                    <p class="mt-3 text-center font-mono text-2xl font-bold tracking-widest text-marca-700 select-all">{{ $cliente->codigoFormateado() }}</p>

                    @php $whatsapp = $cliente->enlaceWhatsApp(auth()->user()->negocio ?? auth()->user()->nombre); @endphp
                    <div class="mt-3 flex flex-col gap-2">
                        @if ($whatsapp)
                            <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn w-full bg-emerald-600 text-white hover:bg-emerald-700">
                                Enviar por WhatsApp
                            </a>
                        @else
                            <p class="text-xs text-slate-500">Registre el teléfono del cliente para enviarle el código por WhatsApp.</p>
                        @endif
                        <form method="POST" action="{{ route('clientes.codigo', $cliente) }}" class="text-center"
                              data-confirmar="¿Generar un código nuevo? El código actual dejará de funcionar.">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-slate-600 hover:text-slate-900 hover:underline">Generar un código nuevo</button>
                        </form>
                    </div>
                @endif
            </div>

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
