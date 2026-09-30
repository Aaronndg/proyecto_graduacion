<x-layouts.app :titulo="'Hola, '.strtok(auth()->user()->nombre, ' ')" subtitulo="Así va su negocio hoy.">
    <x-slot:acciones>
        <a href="{{ route('pedidos.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Nuevo pedido</a>
    </x-slot:acciones>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-estadistica titulo="Pedidos por atender" :valor="$pedidosPendientes" icono="reloj" color="bg-amber-50 text-amber-600" :enlace="route('seguimiento')" />
        <x-estadistica titulo="Clientes" :valor="$totalClientes" icono="usuarios" :enlace="route('clientes.index')" />
        <x-estadistica titulo="Productos activos" :valor="$totalProductos" icono="producto" color="bg-violet-50 text-violet-600" :enlace="route('productos.index')" />
    </div>

    <section class="tarjeta mt-6 overflow-hidden">
        <div class="flex items-center justify-between px-5 pt-5 pb-3">
            <h2 class="font-semibold text-stone-900">Pedidos recientes</h2>
            <a href="{{ route('pedidos.index') }}" class="enlace text-sm">Ver todos</a>
        </div>

        @if ($pedidosRecientes->isEmpty())
            <x-vacio titulo="Aún no hay pedidos"
                     :texto="$totalProductos ? 'Registre su primer pedido y aparecerá aquí.' : 'Empiece agregando sus productos; luego podrá registrar pedidos.'">
                <a href="{{ $totalProductos ? route('pedidos.create') : route('productos.create') }}" class="btn btn-primario">
                    {{ $totalProductos ? 'Registrar pedido' : 'Agregar productos' }}
                </a>
            </x-vacio>
        @else
            <div class="overflow-x-auto">
                <table class="tabla">
                    <thead>
                        <tr><th>Pedido</th><th>Cliente</th><th>Fecha</th><th>Estado</th><th class="text-right">Total</th></tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($pedidosRecientes as $pedido)
                            <tr>
                                <td><a href="{{ route('pedidos.show', $pedido) }}" class="enlace">#{{ $pedido->numero() }}</a></td>
                                <td class="font-medium text-stone-900">{{ $pedido->cliente->nombre }}</td>
                                <td class="whitespace-nowrap text-stone-500">{{ $pedido->fecha->format('d/m/Y') }}</td>
                                <td><x-estado-pedido :estado="$pedido->estado" /></td>
                                <td class="text-right font-medium"><x-moneda :valor="$pedido->total" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.app>
