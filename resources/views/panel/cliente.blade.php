<x-layouts.app titulo="Mis pedidos">
    <div class="mb-6">
        <p class="text-slate-500">Hola, <span class="font-medium text-slate-700">{{ auth()->user()->nombre }}</span>. Consulte aquí el estado de sus pedidos.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-estadistica titulo="Pedidos en curso" :valor="$enCurso" icono="reloj" color="bg-amber-50 text-amber-600" />
        <x-estadistica titulo="Total de pedidos" :valor="$pedidos->count()" icono="pedido" />
    </div>

    <section class="tarjeta mt-6">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Mis pedidos</h2>
        </div>

        @if ($pedidos->isEmpty())
            <div class="px-5 py-12 text-center">
                <x-icono nombre="pedido" clase="mx-auto size-10 text-slate-300" />
                <p class="mt-3 font-medium text-slate-700">Todavía no tiene pedidos asociados</p>
                <p class="mt-1 text-sm text-slate-500">Sus pedidos aparecerán aquí cuando el emprendedor los registre con su correo ({{ auth()->user()->correo }}).</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="tabla">
                    <thead>
                        <tr><th>No.</th><th>Negocio</th><th>Fecha</th><th>Estado</th><th class="text-right">Total</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($pedidos as $pedido)
                            <tr>
                                <td class="font-medium text-slate-900">#{{ $pedido->numero() }}</td>
                                <td>{{ $pedido->emprendedor->negocio ?? $pedido->emprendedor->nombre }}</td>
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
