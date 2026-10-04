{{-- Una línea del pedido: producto, cantidad (− / +), precio y subtotal.
     Teléfono: producto + quitar arriba; cantidad, precio y subtotal abajo (RNF-05). --}}
<li data-linea class="grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-x-3 gap-y-2 border-b border-borde px-5 py-4 md:grid-cols-[minmax(0,1fr)_8.5rem_6rem_6.5rem_2.5rem] md:gap-x-4 md:py-3">
    <div class="col-span-2 row-start-1 md:col-span-1">
        <label for="producto_{{ $indice }}" class="sr-only">Producto</label>
        <select id="producto_{{ $indice }}" name="productos[{{ $indice }}][id_producto]" class="campo" data-producto>
            <option value="">Elija un producto…</option>
            @foreach ($productos as $producto)
                <option value="{{ $producto->id_producto }}" @selected((string) $linea['id_producto'] === (string) $producto->id_producto)>
                    {{ $producto->nombre }}{{ $producto->estado ? '' : ' (inactivo)' }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-start-1 row-start-2 flex h-11 w-[7.5rem] items-stretch overflow-hidden rounded-lg border border-borde-control bg-superficie focus-within:border-marca focus-within:ring-3 focus-within:ring-marca/25 sm:h-10 md:w-[8.5rem] md:col-start-2 md:row-start-1">
        <button type="button" class="flex w-9 shrink-0 cursor-pointer md:w-10 items-center justify-center text-texto-2 hover:bg-superficie-2 hover:text-texto" data-restar>
            <x-icono nombre="menos" clase="size-4" /><span class="sr-only">Quitar uno</span>
        </button>
        <label for="cantidad_{{ $indice }}" class="sr-only">Cantidad</label>
        <input id="cantidad_{{ $indice }}" name="productos[{{ $indice }}][cantidad]" type="number" min="1" max="9999" step="1"
               inputmode="numeric" value="{{ $linea['cantidad'] }}"
               class="w-full min-w-0 [appearance:textfield] border-x border-borde bg-transparent text-center text-base tabular-nums focus:outline-none sm:text-sm [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" data-cantidad>
        <button type="button" class="flex w-9 shrink-0 cursor-pointer md:w-10 items-center justify-center text-texto-2 hover:bg-superficie-2 hover:text-texto" data-sumar>
            <x-icono nombre="mas" clase="size-4" /><span class="sr-only">Agregar uno</span>
        </button>
    </div>

    <span class="col-start-2 row-start-2 text-sm whitespace-nowrap text-texto-2 tabular-nums md:col-start-3 md:row-start-1 md:text-right">
        <span class="md:hidden">× </span><span data-precio>—</span>
    </span>
    <span class="col-start-3 row-start-2 text-right font-medium tabular-nums md:col-start-4 md:row-start-1" data-subtotal>—</span>

    <div class="col-start-3 row-start-1 flex justify-end md:col-start-5">
        <button type="button" class="flex size-10 cursor-pointer items-center justify-center rounded-lg text-texto-2 hover:bg-red-50 hover:text-red-700" data-quitar-linea title="Quitar producto">
            <x-icono nombre="cerrar" clase="size-5" />
            <span class="sr-only">Quitar producto</span>
        </button>
    </div>
</li>
