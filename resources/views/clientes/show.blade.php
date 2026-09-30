<x-layouts.app :titulo="$cliente->nombre" :subtitulo="'Cliente desde '.$cliente->created_at?->translatedFormat('F \d\e Y')">
    <x-slot:acciones>
        <a href="{{ route('clientes.index') }}" class="btn btn-secundario">&larr; Clientes</a>
        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-secundario"><x-icono nombre="editar" clase="size-4" /> Editar</a>
        <a href="{{ route('pedidos.create', ['cliente' => $cliente->id_cliente]) }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Nuevo pedido</a>
    </x-slot:acciones>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <section class="tarjeta p-5">
                <h2 class="font-semibold text-stone-900">Datos de contacto</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    @foreach (['Teléfono' => $cliente->telefono, 'Correo' => $cliente->correo, 'Dirección' => $cliente->direccion] as $dato => $valor)
                        <div>
                            <dt class="text-stone-500">{{ $dato }}</dt>
                            <dd @class(['mt-0.5 break-words', 'font-medium text-stone-900' => $valor, 'text-stone-400' => ! $valor])>{{ $valor ?? 'Sin registrar' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <x-invitacion-cliente :cliente="$cliente" />

            @if ($pedidos->total() === 0)
                <form method="POST" action="{{ route('clientes.destroy', $cliente) }}" class="text-center"
                      data-confirmar="¿Eliminar a {{ $cliente->nombre }}? Esta acción no se puede deshacer.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-medium text-red-600 hover:underline">Eliminar cliente</button>
                </form>
            @endif
        </div>

        <section class="tarjeta overflow-hidden lg:col-span-2">
            <h2 class="px-5 pt-5 pb-3 font-semibold text-stone-900">Pedidos de {{ strtok($cliente->nombre, ' ') }}</h2>

            @if ($pedidos->isEmpty())
                <x-vacio titulo="Aún no tiene pedidos">
                    <a href="{{ route('pedidos.create', ['cliente' => $cliente->id_cliente]) }}" class="btn btn-primario">Registrar su primer pedido</a>
                </x-vacio>
            @else
                <div class="overflow-x-auto">
                    <table class="tabla">
                        <thead><tr><th>Pedido</th><th>Fecha</th><th>Estado</th><th class="text-right">Total</th></tr></thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($pedidos as $pedido)
                                <tr>
                                    <td><a href="{{ route('pedidos.show', $pedido) }}" class="enlace">#{{ $pedido->numero() }}</a></td>
                                    <td class="whitespace-nowrap">{{ $pedido->fecha->format('d/m/Y') }}</td>
                                    <td><x-estado-pedido :estado="$pedido->estado" /></td>
                                    <td class="text-right font-medium"><x-moneda :valor="$pedido->total" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($pedidos->hasPages())<div class="border-t border-stone-100 px-5 py-3">{{ $pedidos->links() }}</div>@endif
            @endif
        </section>
    </div>
</x-layouts.app>
