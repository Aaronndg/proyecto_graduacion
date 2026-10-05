@php
    use App\Http\Requests\PedidoRequest;

    $editando = $pedido->exists;
    $modoCliente = old('modo_cliente', 'existente');
    $catalogo = $productos->mapWithKeys(fn ($p) => [$p->id_producto => [
        'nombre' => $p->nombre,
        'precio' => (float) ($preciosRegistrados[$p->id_producto] ?? $p->precio),
        'imagen' => $p->urlImagen(),
    ]]);
    $errorProductos = $errors->first('productos') ?: collect($errors->getMessages())
        ->filter(fn ($m, $clave) => str_starts_with($clave, 'productos.'))->flatten()->first();
    $volver = $editando ? route('pedidos.show', $pedido) : route('pedidos.index');
    $ruta = $editando ? ['Pedidos' => route('pedidos.index'), '#'.$pedido->numero() => route('pedidos.show', $pedido)] : ['Pedidos' => route('pedidos.index')];

    // Correo y dirección del cliente nuevo son opcionales: se muestran abiertos solo si ya traen algo.
    $extrasCliente = old('nuevo_cliente.correo') || old('nuevo_cliente.direccion')
        || $errors->has('nuevo_cliente.correo') || $errors->has('nuevo_cliente.direccion');

    // Fecha: casi siempre es «ahora»; el campo aparece al pedir cambiarla (o si trae un error).
    $fecha = (old('fecha') ? rescue(fn () => \Illuminate\Support\Carbon::parse(old('fecha')), null, false) : null) ?? $pedido->fecha;
    $fechaAbierta = $errors->has('fecha');
    $textoFecha = match (true) {
        $fecha->isToday() => 'hoy a las '.$fecha->format('H:i'),
        $fecha->isYesterday() => 'ayer a las '.$fecha->format('H:i'),
        default => 'el '.$fecha->translatedFormat('j \d\e F \d\e Y').' a las '.$fecha->format('H:i'),
    };
@endphp
<x-layouts.app :titulo="$editando ? 'Editar pedido' : 'Nuevo pedido'" :ruta="$ruta">
    @if ($editando)
        <x-slot:acciones>
            <x-estado-pedido :estado="$pedido->estado" />
        </x-slot:acciones>
    @endif

    @if ($errors->any())
        <div role="alert" class="mb-6 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <x-icono nombre="alerta" clase="size-5 shrink-0 text-red-600" />
            <span>
                @if ($errors->has('pedido'))
                    {{ $errors->first('pedido') }}
                @else
                    No pudimos guardar el pedido. Revise los campos marcados.
                @endif
            </span>
        </div>
    @endif

    <form method="POST" action="{{ $editando ? route('pedidos.update', $pedido) : route('pedidos.store') }}"
          class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start"
          novalidate data-envio-unico data-formulario-pedido data-catalogo="{{ json_encode($catalogo) }}">
        @csrf
        @if ($editando) @method('PUT') @endif

        <div class="space-y-6">
            {{-- 1. ¿Para quién es? --}}
            <section class="panel space-y-4 p-5" aria-labelledby="titulo-cliente">
                <h2 id="titulo-cliente" class="titulo-seccion">Cliente</h2>

                <div class="flex gap-1 rounded-lg bg-superficie-2 p-1 text-sm font-medium" role="radiogroup" aria-label="Tipo de cliente">
                    @foreach (['existente' => 'Cliente registrado', 'nuevo' => 'Cliente nuevo'] as $valor => $texto)
                        <label class="flex min-h-10 flex-1 cursor-pointer items-center justify-center rounded-md px-3 text-center text-texto-2 has-checked:bg-superficie has-checked:text-texto has-checked:shadow-sm has-focus-visible:outline-2 has-focus-visible:outline-marca">
                            <input type="radio" name="modo_cliente" value="{{ $valor }}" class="sr-only" @checked($modoCliente === $valor) data-modo-cliente>
                            {{ $texto }}
                        </label>
                    @endforeach
                </div>

                <div data-seccion-cliente="existente" @class(['hidden' => $modoCliente !== 'existente'])>
                    <label for="id_cliente" class="etiqueta">¿Para quién es el pedido?</label>
                    <select id="id_cliente" name="id_cliente" data-cliente
                            @class(['campo', 'campo-error' => $errors->has('id_cliente')])
                            @error('id_cliente') aria-invalid="true" aria-describedby="id_cliente-error" @enderror>
                        <option value="">Elija un cliente…</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id_cliente }}" @selected((int) old('id_cliente', $pedido->id_cliente) === $cliente->id_cliente)>
                                {{ $cliente->nombre }}{{ $cliente->telefono ? ' · '.$cliente->telefono : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_cliente')<p id="id_cliente-error" class="error-campo">{{ $message }}</p>@enderror
                    @if ($clientes->isEmpty())
                        <p class="ayuda">Aún no tiene clientes. Elija «Cliente nuevo» para registrarlo junto con el pedido.</p>
                    @endif
                </div>

                <div data-seccion-cliente="nuevo" @class(['space-y-4', 'hidden' => $modoCliente !== 'nuevo'])>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-campo nombre="nuevo_cliente[nombre]" id="nuevo_nombre" etiqueta="Nombre del cliente" maxlength="100" requerido autocomplete="off" data-nombre-nuevo />
                        <x-campo nombre="nuevo_cliente[telefono]" id="nuevo_telefono" etiqueta="Teléfono" tipo="tel" placeholder="5555-5555" inputmode="tel" maxlength="20" autocomplete="off" />
                    </div>
                    <details class="group" @if ($extrasCliente) open @endif>
                        <summary class="inline-flex min-h-9 cursor-pointer list-none items-center gap-1.5 text-sm text-texto-2 hover:text-texto [&::-webkit-details-marker]:hidden">
                            <x-icono nombre="flecha-derecha" clase="size-4 transition-transform group-open:rotate-90" />
                            Agregar correo y dirección (opcional)
                        </summary>
                        <div class="mt-3 grid gap-4 sm:grid-cols-2">
                            <x-campo nombre="nuevo_cliente[correo]" id="nuevo_correo" etiqueta="Correo electrónico" tipo="email" maxlength="150" autocomplete="off" />
                            <x-campo nombre="nuevo_cliente[direccion]" id="nuevo_direccion" etiqueta="Dirección" maxlength="255" autocomplete="off" />
                        </div>
                    </details>
                </div>
            </section>

            {{-- 2. ¿Qué pidió? --}}
            <section class="panel overflow-hidden" aria-labelledby="titulo-productos">
                <h2 id="titulo-productos" class="titulo-seccion px-5 pt-4 pb-2">Productos</h2>

                @if ($productos->isEmpty())
                    <x-vacio ilustracion="caja" titulo="No tiene productos activos" texto="Registre lo que vende en su catálogo para poder crear pedidos.">
                        <a href="{{ route('productos.create') }}" class="btn btn-primario">Registrar producto</a>
                    </x-vacio>
                @else
                    @if ($errorProductos)
                        <p role="alert" class="error-campo mx-5 mb-2">{{ $errorProductos }}</p>
                    @endif

                    {{-- Elegir con fotos: un toque agrega el producto (o suma uno si ya está en el pedido) --}}
                    @php $disponibles = $productos->where('estado', true); @endphp
                    <div class="border-t border-borde px-5 pt-3 pb-4">
                        <p class="meta mb-2.5">Toque un producto para agregarlo.</p>
                        <ul class="-mx-5 flex snap-x scroll-px-5 gap-3 overflow-x-auto px-5 pt-1 pb-1" aria-label="Productos disponibles">
                            @foreach ($disponibles as $producto)
                                <li class="shrink-0 snap-start">
                                    <button type="button" data-elegir-producto="{{ $producto->id_producto }}" aria-pressed="false"
                                            class="group relative flex w-28 cursor-pointer flex-col overflow-hidden rounded-2xl bg-superficie text-left shadow-[0_0_0_1px_var(--color-borde)] transition hover:-translate-y-0.5 hover:shadow-suave aria-pressed:shadow-[0_0_0_2px_var(--color-marca)]">
                                        <span class="block aspect-square overflow-hidden"><x-foto-producto :producto="$producto" icono="size-9" /></span>
                                        <span class="px-2.5 pt-1.5 pb-2 text-[13px] leading-tight">
                                            <span class="line-clamp-2 font-bold">{{ $producto->nombre }}</span>
                                            <span class="meta block"><x-moneda :valor="$catalogo[$producto->id_producto]['precio']" /></span>
                                        </span>
                                        <span class="absolute top-1.5 right-1.5 flex size-7 items-center justify-center rounded-full bg-oro text-sm font-extrabold text-stone-950 shadow-md group-aria-pressed:bg-marca group-aria-pressed:text-white" data-ficha-cantidad>+</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="hidden grid-cols-[minmax(0,1fr)_8.5rem_6rem_6.5rem_2.5rem] gap-x-4 border-y border-borde bg-superficie-2 px-5 py-2 text-[13px] font-medium text-texto-2 md:grid" aria-hidden="true">
                        <span>Producto</span><span>Cantidad</span><span class="text-right">Precio</span><span class="text-right">Subtotal</span><span></span>
                    </div>

                    <ul class="border-t border-borde md:border-t-0" data-lineas>
                        @foreach ($lineas as $i => $linea)
                            @include('pedidos.partials.linea', ['indice' => $i, 'linea' => $linea])
                        @endforeach
                    </ul>

                    <div class="px-5 py-3">
                        <button type="button" class="btn btn-terciario -ml-2.5" data-agregar-linea>
                            <x-icono nombre="mas" clase="size-4" /> Agregar otro producto
                        </button>
                    </div>

                    <template data-plantilla-linea>
                        @include('pedidos.partials.linea', ['indice' => '__INDICE__', 'linea' => ['id_producto' => '', 'cantidad' => 1]])
                    </template>
                @endif
            </section>

            {{-- 3. ¿Cuándo? Normalmente ahora mismo --}}
            <section class="panel p-5" aria-labelledby="titulo-fecha">
                <h2 id="titulo-fecha" class="sr-only">Fecha del pedido</h2>
                <details class="group" @if ($fechaAbierta) open @endif>
                    <summary class="flex cursor-pointer list-none flex-wrap items-center gap-x-2 text-sm [&::-webkit-details-marker]:hidden">
                        <x-icono nombre="reloj" clase="size-5 text-texto-2" />
                        <span>{{ $editando ? 'Registrado' : 'Se registra' }} {{ $textoFecha }}</span>
                        <span class="enlace group-open:hidden">Cambiar</span>
                    </summary>
                    <div class="mt-4 max-w-xs">
                        <x-campo nombre="fecha" etiqueta="Fecha y hora del pedido" tipo="datetime-local" requerido
                                 :valor="$pedido->fecha?->format('Y-m-d\TH:i')"
                                 min="{{ PedidoRequest::fechaMinima($editando ? $pedido : null) }}" max="{{ PedidoRequest::fechaMaxima() }}" />
                    </div>
                </details>
            </section>
        </div>

        {{-- Resumen: panel que acompaña en escritorio; barra fija sobre la navegación en el teléfono --}}
        <aside class="sticky bottom-[calc(4rem+1px+env(safe-area-inset-bottom))] z-20 -mx-4 border-t border-borde bg-superficie px-4 py-3 sm:-mx-6 sm:px-6
                      lg:top-8 lg:bottom-auto lg:mx-0 lg:rounded-2xl lg:border lg:p-5" aria-label="Resumen del pedido">
            <dl class="hidden space-y-2 border-b border-borde pb-4 text-sm lg:block">
                <div class="flex justify-between gap-3"><dt class="text-texto-2">Cliente</dt><dd class="truncate text-right font-medium" data-resumen-cliente>—</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-texto-2">Productos</dt><dd class="font-medium tabular-nums" data-resumen-productos>—</dd></div>
            </dl>
            <div class="flex items-center justify-between gap-4 lg:mt-4 lg:block">
                <div class="lg:flex lg:items-baseline lg:justify-between">
                    <span class="block text-[13px] text-texto-2 lg:text-sm lg:font-medium lg:text-texto">Total</span>
                    <span class="text-lg font-semibold tabular-nums lg:text-xl" data-total>Q 0.00</span>
                </div>
                <button type="submit" class="btn btn-primario lg:mt-4 lg:w-full" @disabled($productos->isEmpty())>
                    {{ $editando ? 'Guardar cambios' : 'Crear pedido' }}
                </button>
            </div>
            <a href="{{ $volver }}" class="btn btn-terciario mt-2 hidden w-full lg:flex">Cancelar</a>
            @if ($editando)
                <p class="ayuda hidden lg:block">Los productos que ya estaban en el pedido conservan el precio con el que se registraron.</p>
            @endif
        </aside>
    </form>
</x-layouts.app>
