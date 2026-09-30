<x-layouts.app titulo="Panel principal">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-slate-500">¡Bienvenido de nuevo, <span class="font-medium text-slate-700">{{ auth()->user()->nombre }}</span>! Aquí tiene un resumen de su negocio.</p>
        <a href="{{ route('pedidos.create') }}" class="btn btn-primario self-start"><x-icono nombre="mas" clase="size-4" /> Nuevo pedido</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-estadistica titulo="Clientes" :valor="$totalClientes" icono="usuarios" />
        <x-estadistica titulo="Pedidos pendientes" :valor="$pedidosPendientes" icono="pedido" color="bg-amber-50 text-amber-600" />
        <x-estadistica titulo="Productos activos" :valor="$totalProductos" icono="producto" color="bg-emerald-50 text-emerald-600" />
    </div>

    <section class="tarjeta mt-6">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Pedidos recientes</h2>
            <a href="{{ route('pedidos.index') }}" class="text-sm font-medium text-marca-600 hover:underline">Ver todos</a>
        </div>

        @if ($pedidosRecientes->isEmpty())
            <x-vacio titulo="Aún no hay pedidos registrados"
                     :texto="$totalProductos ? 'Registre su primer pedido para verlo aquí.' : 'Empiece registrando sus productos; luego podrá crear pedidos.'">
                <a href="{{ $totalProductos ? route('pedidos.create') : route('productos.create') }}" class="btn btn-primario">
                    {{ $totalProductos ? 'Registrar pedido' : 'Registrar productos' }}
                </a>
            </x-vacio>
        @else
            <div class="overflow-x-auto">
                <table class="tabla">
                    <thead>
                        <tr><th>No.</th><th>Cliente</th><th>Fecha</th><th>Estado</th><th class="text-right">Total</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($pedidosRecientes as $pedido)
                            <tr>
                                <td><a href="{{ route('pedidos.show', $pedido) }}" class="font-medium text-marca-600 hover:underline">#{{ $pedido->numero() }}</a></td>
                                <td>{{ $pedido->cliente->nombre }}</td>
                                <td class="whitespace-nowrap">{{ $pedido->fecha->format('d/m/Y') }}</td>
                                <td><x-estado-pedido :estado="$pedido->estado" /></td>
                                <td class="text-right"><x-moneda :valor="$pedido->total" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.app>
