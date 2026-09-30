<x-layouts.app :titulo="'Pedido #'.$pedido->numero()">
    <a href="{{ route('panel') }}" class="mb-4 inline-block text-sm font-medium text-marca-600 hover:underline">&larr; Volver a mis pedidos</a>

    <section class="tarjeta mb-6 p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Pedido #{{ $pedido->numero() }}</h2>
                <p class="text-sm text-slate-500">
                    {{ $pedido->emprendedor->negocio ?? $pedido->emprendedor->nombre }} ·
                    {{ $pedido->fecha->translatedFormat('d \d\e F \d\e Y, H:i') }}
                </p>
            </div>
            <x-estado-pedido :estado="$pedido->estado" />
        </div>

        <x-progreso-pedido :pedido="$pedido" class="mt-6 border-t border-slate-200 pt-5" />
    </section>

    <div class="grid gap-6 lg:grid-cols-5">
        <section class="tarjeta lg:col-span-3">
            <h2 class="border-b border-slate-200 px-5 py-4 font-semibold text-slate-900">Productos</h2>
            <div class="overflow-x-auto">
                <table class="tabla">
                    <thead><tr><th>Producto</th><th class="text-center">Cantidad</th><th class="text-right">Subtotal</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($pedido->detalles as $detalle)
                            <tr>
                                <td class="font-medium text-slate-900">{{ $detalle->producto->nombre }}</td>
                                <td class="text-center tabular-nums">{{ $detalle->cantidad }}</td>
                                <td class="text-right"><x-moneda :valor="$detalle->subtotal" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50">
                            <td colspan="2" class="text-right text-sm font-semibold text-slate-700 uppercase">Total</td>
                            <td class="text-right font-bold text-slate-900"><x-moneda :valor="$pedido->total" /></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <section class="tarjeta lg:col-span-2">
            <h2 class="border-b border-slate-200 px-5 py-4 font-semibold text-slate-900">Historial del pedido</h2>
            <ol class="space-y-4 p-5">
                @foreach ($pedido->historial->reverse() as $registro)
                    <li class="flex gap-3">
                        <span class="mt-1.5 size-2.5 shrink-0 rounded-full bg-marca-500" aria-hidden="true"></span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-estado-pedido :estado="$registro->estado" />
                                <span class="text-xs text-slate-500">{{ $registro->fecha_hora->format('d/m/Y H:i') }}</span>
                            </div>
                            @if ($registro->observacion)<p class="mt-1 text-sm text-slate-700">{{ $registro->observacion }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
</x-layouts.app>
