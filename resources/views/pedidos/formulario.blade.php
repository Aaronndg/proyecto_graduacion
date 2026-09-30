@php
    $editando = $pedido->exists;
    $modoCliente = old('modo_cliente', 'existente');
    $catalogo = $productos->mapWithKeys(fn ($p) => [$p->id_producto => [
        'nombre' => $p->nombre,
        'precio' => (float) ($preciosRegistrados[$p->id_producto] ?? $p->precio),
    ]]);
    $errorProductos = $errors->first('productos') ?: collect($errors->getMessages())
        ->filter(fn ($m, $clave) => str_starts_with($clave, 'productos.'))->flatten()->first();
@endphp
<x-layouts.app :titulo="$editando ? 'Editar pedido #'.$pedido->numero() : 'Nuevo pedido'">
    <div class="mx-auto max-w-4xl">
        <a href="{{ $editando ? route('pedidos.show', $pedido) : route('pedidos.index') }}" class="mb-4 inline-block text-sm font-medium text-marca-600 hover:underline">&larr; Volver</a>

        @if ($errors->has('pedido'))
            <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first('pedido') }}</div>
        @endif

        <form method="POST" action="{{ $editando ? route('pedidos.update', $pedido) : route('pedidos.store') }}"
              class="space-y-6" novalidate data-envio-unico data-formulario-pedido data-catalogo="{{ json_encode($catalogo) }}">
            @csrf
            @if ($editando) @method('PUT') @endif

            {{-- Cliente y fecha --}}
            <section class="tarjeta space-y-5 p-6">
                <h2 class="text-base font-semibold text-slate-900">Datos del pedido</h2>

                <div class="flex gap-2 rounded-lg bg-slate-100 p-1 text-sm font-medium" role="radiogroup" aria-label="Cliente">
                    @foreach (['existente' => 'Cliente registrado', 'nuevo' => 'Cliente nuevo'] as $valor => $texto)
                        <label class="flex-1 cursor-pointer rounded-md px-3 py-2 text-center text-slate-600 has-checked:bg-white has-checked:text-marca-700 has-checked:shadow-sm">
                            <input type="radio" name="modo_cliente" value="{{ $valor }}" class="sr-only" @checked($modoCliente === $valor) data-modo-cliente>
                            {{ $texto }}
                        </label>
                    @endforeach
                </div>

                <div data-seccion-cliente="existente" @class(['hidden' => $modoCliente !== 'existente'])>
                    <label for="id_cliente" class="etiqueta">Cliente <span class="text-red-600" aria-hidden="true">*</span></label>
                    <select id="id_cliente" name="id_cliente" @class(['campo', 'campo-error' => $errors->has('id_cliente')])>
                        <option value="">Seleccione un cliente…</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id_cliente }}" @selected((int) old('id_cliente', $pedido->id_cliente) === $cliente->id_cliente)>
                                {{ $cliente->nombre }}{{ $cliente->telefono ? ' · '.$cliente->telefono : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_cliente')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @if ($clientes->isEmpty())
                        <p class="mt-1 text-xs text-slate-500">Aún no tiene clientes. Elija «Cliente nuevo» para registrarlo junto con el pedido.</p>
                    @endif
                </div>

                <div data-seccion-cliente="nuevo" @class(['grid gap-4 sm:grid-cols-2', 'hidden' => $modoCliente !== 'nuevo'])>
                    <x-campo nombre="nuevo_cliente[nombre]" id="nuevo_nombre" etiqueta="Nombre del cliente" requerido />
                    <x-campo nombre="nuevo_cliente[telefono]" id="nuevo_telefono" etiqueta="Teléfono" tipo="tel" placeholder="5555-5555" />
                    <x-campo nombre="nuevo_cliente[correo]" id="nuevo_correo" etiqueta="Correo electrónico" tipo="email" />
                    <x-campo nombre="nuevo_cliente[direccion]" id="nuevo_direccion" etiqueta="Dirección" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-campo nombre="fecha" etiqueta="Fecha y hora del pedido" tipo="datetime-local" requerido
                             :valor="$pedido->fecha?->format('Y-m-d\TH:i')" />
                    <div>
                        <span class="etiqueta">Estado</span>
                        <p class="flex h-[38px] items-center">
                            @if ($editando)
                                <x-estado-pedido :estado="$pedido->estado" />
                            @else
                                <span class="text-sm text-slate-600">Se registrará como <strong>Nuevo</strong></span>
                            @endif
                        </p>
                    </div>
                </div>
            </section>

            {{-- Productos --}}
            <section class="tarjeta">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Productos</h2>
                    <button type="button" class="btn btn-secundario px-3 py-1.5" data-agregar-linea @disabled($productos->isEmpty())>
                        <x-icono nombre="mas" clase="size-4" /> Agregar producto
                    </button>
                </div>

                @if ($productos->isEmpty())
                    <x-vacio icono="producto" titulo="No tiene productos activos" texto="Registre productos en su catálogo para poder crear pedidos.">
                        <a href="{{ route('productos.create') }}" class="btn btn-primario">Registrar producto</a>
                    </x-vacio>
                @else
                    @if ($errorProductos)
                        <p role="alert" class="mx-5 mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errorProductos }}</p>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th class="min-w-48">Producto</th>
                                    <th class="w-28">Cantidad</th>
                                    <th class="w-28 text-right">Precio</th>
                                    <th class="w-32 text-right">Subtotal</th>
                                    <th class="w-12"><span class="sr-only">Quitar</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100" data-lineas>
                                @foreach ($lineas as $i => $linea)
                                    @include('pedidos.partials.linea', ['indice' => $i, 'linea' => $linea])
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-50">
                                    <td colspan="3" class="text-right text-sm font-semibold text-slate-700 uppercase">Total</td>
                                    <td class="text-right text-lg font-bold text-slate-900 tabular-nums" data-total>Q 0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <p class="px-5 py-3 text-xs text-slate-500">
                        El total se calcula con los precios del catálogo y se verifica al guardar.
                        @if ($editando) Los productos que ya estaban en el pedido conservan el precio con el que se registraron. @endif
                    </p>

                    <template data-plantilla-linea>
                        @include('pedidos.partials.linea', ['indice' => '__INDICE__', 'linea' => ['id_producto' => '', 'cantidad' => 1]])
                    </template>
                @endif
            </section>

            <div class="flex justify-end gap-3">
                <a href="{{ $editando ? route('pedidos.show', $pedido) : route('pedidos.index') }}" class="btn btn-secundario">Cancelar</a>
                <button type="submit" class="btn btn-primario" @disabled($productos->isEmpty())>{{ $editando ? 'Guardar cambios' : 'Guardar pedido' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>
