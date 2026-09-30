@php
    use App\Models\EstadoPedido;

    $cancelar = $estadosSiguientes->firstWhere('id_estado', EstadoPedido::CANCELADO);
    $avances = $estadosSiguientes->where('id_estado', '!=', EstadoPedido::CANCELADO)->values();
    $siguiente = $avances->first();
    $saltos = $avances->slice(1);
@endphp
<x-layouts.app :titulo="'Pedido #'.$pedido->numero()"
                :subtitulo="$pedido->cliente->nombre.' · '.$pedido->fecha->translatedFormat('d \d\e F \d\e Y, H:i')">
    <x-slot:acciones>
        <a href="{{ route('pedidos.index') }}" class="btn btn-secundario">&larr; Pedidos</a>
        @if ($editable)
            <a href="{{ route('pedidos.edit', $pedido) }}" class="btn btn-secundario"><x-icono nombre="editar" clase="size-4" /> Editar</a>
        @endif
    </x-slot:acciones>

    {{-- Estado actual y progreso --}}
    <section class="tarjeta mb-6 p-5 sm:p-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <x-estado-pedido :estado="$pedido->estado" />
                <span class="text-sm text-stone-500">{{ $pedido->estado->descripcion }}</span>
            </div>
            <p class="text-sm text-stone-500">Total <x-moneda :valor="$pedido->total" class="ml-1 text-lg font-bold text-stone-900" /></p>
        </div>
        <x-progreso-pedido :pedido="$pedido" />
    </section>

    <div class="grid gap-6 lg:grid-cols-5">
        <div class="space-y-6 lg:col-span-3">
            {{-- Productos del pedido --}}
            <section class="tarjeta overflow-hidden">
                <h2 class="px-5 pt-5 pb-3 font-semibold text-stone-900">Productos</h2>
                <div class="overflow-x-auto">
                    <table class="tabla">
                        <thead>
                            <tr><th>Producto</th><th class="text-center">Cantidad</th><th class="text-right">Precio</th><th class="text-right">Subtotal</th></tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($pedido->detalles as $detalle)
                                <tr>
                                    <td class="font-medium text-stone-900">{{ $detalle->producto->nombre }}</td>
                                    <td class="text-center tabular-nums">{{ $detalle->cantidad }}</td>
                                    <td class="text-right text-stone-500"><x-moneda :valor="$detalle->precio_unitario" /></td>
                                    <td class="text-right font-medium"><x-moneda :valor="$detalle->subtotal" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-stone-50">
                                <td colspan="3" class="text-right font-semibold text-stone-700">Total</td>
                                <td class="text-right text-base font-bold text-stone-900"><x-moneda :valor="$pedido->total" /></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            {{-- Cliente --}}
            <section class="tarjeta flex flex-wrap items-center justify-between gap-4 p-5">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-marca-50 font-semibold text-marca-700">{{ mb_strtoupper(mb_substr($pedido->cliente->nombre, 0, 1)) }}</span>
                    <div>
                        <p class="font-semibold text-stone-900">{{ $pedido->cliente->nombre }}</p>
                        <p class="text-sm text-stone-500">{{ collect([$pedido->cliente->telefono, $pedido->cliente->correo])->filter()->implode(' · ') ?: 'Sin datos de contacto' }}</p>
                    </div>
                </div>
                <a href="{{ route('clientes.show', $pedido->cliente) }}" class="btn btn-secundario px-3 py-1.5">Ver cliente</a>
            </section>
        </div>

        <div class="space-y-6 lg:col-span-2">
            {{-- Actualizar estado (RF-09): el siguiente paso natural se muestra como botón principal. --}}
            @if ($estadosSiguientes->isNotEmpty())
                <section class="tarjeta p-5">
                    <h2 class="font-semibold text-stone-900">Actualizar estado</h2>

                    <form method="POST" action="{{ route('pedidos.estado', $pedido) }}" class="mt-4 space-y-3" novalidate data-envio-unico data-formulario-estado>
                        @csrf
                        <div>
                            <label for="observacion" class="etiqueta">Nota para el historial <span class="font-normal text-stone-400">(opcional)</span></label>
                            <textarea id="observacion" name="observacion" rows="2" maxlength="255" placeholder="Ej.: Sale a entrega a las 3 p. m."
                                      @class(['campo', 'campo-error' => $errors->has('observacion')])>{{ old('observacion') }}</textarea>
                            @error('observacion')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            @error('id_estado')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        @if ($siguiente)
                            <button type="submit" name="id_estado" value="{{ $siguiente->id_estado }}" class="btn btn-primario w-full py-3">
                                {{ $siguiente->accion() }} &rarr;
                            </button>
                        @endif

                        @if ($saltos->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach ($saltos as $estado)
                                    <button type="submit" name="id_estado" value="{{ $estado->id_estado }}" class="btn btn-secundario flex-1 py-2 text-xs">
                                        Pasar a «{{ $estado->nombre }}»
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if ($cancelar)
                            <div class="border-t border-stone-100 pt-3 text-center">
                                <button type="submit" name="id_estado" value="{{ $cancelar->id_estado }}" data-cancelar
                                        class="cursor-pointer text-sm font-medium text-red-600 hover:underline">
                                    Cancelar pedido
                                </button>
                            </div>
                        @endif
                    </form>
                </section>
            @endif

            @unless ($pedido->cliente->id_usuario)
                <x-invitacion-cliente :cliente="$pedido->cliente" />
            @endunless

            {{-- Historial de estados (RF-11) --}}
            <section class="tarjeta">
                <h2 class="px-5 pt-5 font-semibold text-stone-900">Historial</h2>
                <ol class="space-y-5 p-5">
                    @foreach ($pedido->historial->reverse() as $registro)
                        <li class="flex gap-3">
                            <span @class(['mt-1.5 size-2.5 shrink-0 rounded-full', 'bg-marca-500 ring-4 ring-marca-100' => $loop->first, 'bg-stone-300' => ! $loop->first]) aria-hidden="true"></span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-estado-pedido :estado="$registro->estado" />
                                    <span class="text-xs text-stone-500">{{ $registro->fecha_hora->format('d/m/Y H:i') }}</span>
                                </div>
                                @if ($registro->observacion)<p class="mt-1 text-sm text-stone-700">{{ $registro->observacion }}</p>@endif
                                @if ($registro->usuario)<p class="text-xs text-stone-400">Por {{ $registro->usuario->nombre }}</p>@endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        </div>
    </div>
</x-layouts.app>
