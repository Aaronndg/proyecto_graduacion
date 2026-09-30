<x-layouts.app :titulo="'Pedido #'.$pedido->numero()">
    <a href="{{ route('pedidos.index') }}" class="mb-4 inline-block text-sm font-medium text-marca-600 hover:underline">&larr; Volver a pedidos</a>

    {{-- Encabezado del pedido --}}
    <section class="tarjeta mb-6 p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="flex size-11 items-center justify-center rounded-full bg-marca-50 text-marca-600"><x-icono nombre="pedido" clase="size-6" /></span>
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Pedido #{{ $pedido->numero() }}</h2>
                    <p class="text-sm text-slate-500">Información general del pedido</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <x-estado-pedido :estado="$pedido->estado" />
                @if ($editable)
                    <a href="{{ route('pedidos.edit', $pedido) }}" class="btn btn-secundario px-3 py-1.5"><x-icono nombre="editar" clase="size-4" /> Editar</a>
                @endif
            </div>
        </div>

        <dl class="mt-5 grid gap-4 sm:grid-cols-3">
            <div class="rounded-lg bg-slate-50 p-4">
                <dt class="text-xs text-slate-500">Cliente</dt>
                <dd class="mt-1 font-semibold text-slate-900">
                    <a href="{{ route('clientes.show', $pedido->cliente) }}" class="hover:text-marca-600 hover:underline">{{ $pedido->cliente->nombre }}</a>
                </dd>
                <dd class="text-xs text-slate-500">{{ collect([$pedido->cliente->telefono, $pedido->cliente->correo])->filter()->implode(' · ') ?: 'Sin datos de contacto' }}</dd>
            </div>
            <div class="rounded-lg bg-slate-50 p-4">
                <dt class="text-xs text-slate-500">Fecha del pedido</dt>
                <dd class="mt-1 font-semibold text-slate-900">{{ $pedido->fecha->translatedFormat('d \d\e F \d\e Y') }}</dd>
                <dd class="text-xs text-slate-500">{{ $pedido->fecha->format('H:i') }} h</dd>
            </div>
            <div class="rounded-lg bg-slate-50 p-4">
                <dt class="text-xs text-slate-500">Total del pedido</dt>
                <dd class="mt-1 text-lg font-bold text-slate-900"><x-moneda :valor="$pedido->total" /></dd>
                <dd class="text-xs text-slate-500">{{ $pedido->detalles->sum('cantidad') }} producto(s)</dd>
            </div>
        </dl>
    </section>

    <div class="grid gap-6 lg:grid-cols-5">
        {{-- Productos del pedido --}}
        <section class="tarjeta lg:col-span-3">
            <h2 class="border-b border-slate-200 px-5 py-4 font-semibold text-slate-900">Productos del pedido</h2>
            <div class="overflow-x-auto">
                <table class="tabla">
                    <thead>
                        <tr><th>Producto</th><th class="text-center">Cantidad</th><th class="text-right">Precio unitario</th><th class="text-right">Subtotal</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($pedido->detalles as $detalle)
                            <tr>
                                <td class="font-medium text-slate-900">{{ $detalle->producto->nombre }}</td>
                                <td class="text-center tabular-nums">{{ $detalle->cantidad }}</td>
                                <td class="text-right"><x-moneda :valor="$detalle->precio_unitario" /></td>
                                <td class="text-right"><x-moneda :valor="$detalle->subtotal" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50">
                            <td colspan="3" class="text-right text-sm font-semibold text-slate-700 uppercase">Total</td>
                            <td class="text-right font-bold text-slate-900"><x-moneda :valor="$pedido->total" /></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        {{-- Historial de estados (RF-11) --}}
        <section class="tarjeta lg:col-span-2">
            <h2 class="border-b border-slate-200 px-5 py-4 font-semibold text-slate-900">Historial de estados</h2>
            <ol class="space-y-4 p-5">
                @foreach ($pedido->historial as $registro)
                    <li class="relative flex gap-3">
                        <span class="mt-1.5 size-2.5 shrink-0 rounded-full bg-marca-500" aria-hidden="true"></span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-estado-pedido :estado="$registro->estado" />
                                <span class="text-xs text-slate-500">{{ $registro->fecha_hora->format('d/m/Y H:i') }}</span>
                            </div>
                            @if ($registro->observacion)<p class="mt-1 text-sm text-slate-700">{{ $registro->observacion }}</p>@endif
                            @if ($registro->usuario)<p class="text-xs text-slate-500">Por {{ $registro->usuario->nombre }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
</x-layouts.app>
