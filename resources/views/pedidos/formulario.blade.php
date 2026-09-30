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

    <x-slot:acciones>
        <a href="{{ $editando ? route('pedidos.show', $pedido) : route('pedidos.index') }}" class="btn btn-secundario">&larr; Volver</a>
    </x-slot:acciones>
    <div class="max-w-4xl">

        @if ($errors->has('pedido'))
            <div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first('pedido') }}</div>
        @endif

        <form method="POST" action="{{ $editando ? route('pedidos.update', $pedido) : route('pedidos.store') }}"
              class="space-y-6" novalidate data-envio-unico data-formulario-pedido data-catalogo="{{ json_encode($catalogo) }}">
            @csrf
            @if ($editando) @method('PUT') @endif

            {{-- Cliente y fecha --}}
            <section class="tarjeta space-y-5 p-6">
                <h2 class="text-base font-semibold text-stone-900">Datos del pedido</h2>

                <div class="flex gap-2 rounded-lg bg-stone-100 p-1 text-sm font-medium" role="radiogroup" aria-label="Cliente">
                    @foreach (['existente' => 'Cliente registrado', 'nuevo' => 'Cliente nuevo'] as $valor => $texto)
                        <label class="flex-1 cursor-pointer rounded-md px-3 py-2 text-center text-stone-600 has-checked:bg-white has-checked:text-marca-700 has-checked:shadow-sm">
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
                        <p class="mt-1 text-xs text-stone-500">Aún no tiene clientes. Elija «Cliente nuevo» para registrarlo junto con el pedido.</p>
                    @endif
                </div>

                <div data-seccion-cliente="nuevo" @class(['grid gap-4 sm:grid-cols-2', 'hidden' => $modoCliente !== 'nuevo'])>
                    <x-campo nombre="nuevo_cliente[nombre]" id="nuevo_nombre" etiqueta="Nombre del cliente" maxlength="100" requerido />
                    <x-campo nombre="nuevo_cliente[telefono]" id="nuevo_telefono" etiqueta="Teléfono" tipo="tel" placeholder="5555-5555" inputmode="tel" maxlength="20" />
                    <x-campo nombre="nuevo_cliente[correo]" id="nuevo_correo" etiqueta="Correo electrónico" tipo="email" maxlength="150" />
                    <x-campo nombre="nuevo_cliente[direccion]" id="nuevo_direccion" etiqueta="Dirección" maxlength="255" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-campo nombre="fecha" etiqueta="Fecha y hora del pedido" tipo="datetime-local" requerido
                             :valor="$pedido->fecha?->format('Y-m-d\TH:i')"
                             min="{{ \App\Http\Requests\PedidoRequest::fechaMinima($pedido->exists ? $pedido : null) }}" max="{{ \App\Http\Requests\PedidoRequest::fechaMaxima() }}" />
                    <div>
                        <span class="etiqueta">Estado</span>
                        <p class="flex h-[38px] items-center">
                            @if ($editando)
                                <x-estado-pedido :estado="$pedido->estado" />
                            @else
                                <span class="text-sm text-stone-600">Se registrará como <strong>Nuevo</strong></span>
                            @endif
                        </p>
                    </div>
                </div>
            </section>

            {{-- Productos --}}
            <section class="tarjeta">
                <div class="flex items-center justify-between border-b border-stone-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-stone-900">Productos</h2>
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

                    <div class="sm:overflow-x-auto">
                        <table class="tabla block sm:table">
                            <thead class="hidden sm:table-header-group">
                                <tr>
                                    <th class="min-w-48">Producto</th>
                                    <th class="w-28">Cantidad</th>
                                    <th class="w-28 text-right">Precio</th>
                                    <th class="w-32 text-right">Subtotal</th>
                                    <th class="w-12"><span class="sr-only">Quitar</span></th>
                                </tr>
                            </thead>
                            <tbody class="block divide-y divide-stone-100 sm:table-row-group" data-lineas>
                                @foreach ($lineas as $i => $linea)
                                    @include('pedidos.partials.linea', ['indice' => $i, 'linea' => $linea])
                                @endforeach
                            </tbody>
                            <tfoot class="block sm:table-footer-group">
                                <tr class="flex items-center justify-between bg-stone-50 sm:table-row">
                                    <td colspan="3" class="text-right text-sm font-semibold text-stone-700 uppercase">Total</td>
                                    <td class="text-right text-lg font-bold text-stone-900 tabular-nums" data-total>Q 0.00</td>
                                    <td class="hidden sm:table-cell"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <p class="px-5 py-3 text-xs text-stone-500">
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
