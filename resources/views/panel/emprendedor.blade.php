<x-layouts.app titulo="Panel principal">
    <div class="mb-6">
        <p class="text-slate-500">¡Bienvenido de nuevo, <span class="font-medium text-slate-700">{{ auth()->user()->nombre }}</span>! Aquí tiene un resumen de su negocio.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-estadistica titulo="Clientes" :valor="$totalClientes" icono="usuarios" />
        <x-estadistica titulo="Pedidos pendientes" :valor="$pedidosPendientes" icono="pedido" color="bg-amber-50 text-amber-600" />
        <x-estadistica titulo="Productos activos" :valor="$totalProductos" icono="producto" color="bg-emerald-50 text-emerald-600" />
    </div>

    <section class="tarjeta mt-6">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Pedidos recientes</h2>
        </div>

        @if ($pedidosRecientes->isEmpty())
            <div class="px-5 py-12 text-center">
                <x-icono nombre="pedido" clase="mx-auto size-10 text-slate-300" />
                <p class="mt-3 font-medium text-slate-700">Aún no hay pedidos registrados</p>
                <p class="mt-1 text-sm text-slate-500">Cuando registre pedidos, aparecerán aquí.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="tabla">
                    <thead>
                        <tr><th>No.</th><th>Cliente</th><th>Fecha</th><th>Estado</th><th class="text-right">Total</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($pedidosRecientes as $pedido)
                            <tr>
                                <td class="font-medium text-slate-900">#{{ $pedido->numero() }}</td>
                                <td>{{ $pedido->cliente->nombre }}</td>
                                <td class="whitespace-nowrap">{{ $pedido->fecha->format('d/m/Y') }}</td>
                                <td><x-estado-pedido :estado="$pedido->estado" /></td>
                                <td class="text-right tabular-nums">Q {{ number_format($pedido->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.app>
