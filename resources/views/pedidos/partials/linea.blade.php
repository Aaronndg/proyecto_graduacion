{{-- Una línea del detalle del pedido (producto, cantidad, precio y subtotal).
     En el teléfono se muestra en dos filas: producto + quitar / cantidad, precio y subtotal (RNF-05). --}}
<tr data-linea class="grid grid-cols-[6rem_1fr_auto] items-center gap-x-3 gap-y-2 p-4 sm:table-row sm:p-0">
    <td class="col-span-2 row-start-1 p-0 sm:table-cell sm:px-4 sm:py-3.5">
        <label for="producto_{{ $indice }}" class="sr-only">Producto</label>
        <select id="producto_{{ $indice }}" name="productos[{{ $indice }}][id_producto]" class="campo" data-producto>
            <option value="">Seleccione…</option>
            @foreach ($productos as $producto)
                <option value="{{ $producto->id_producto }}" @selected((string) $linea['id_producto'] === (string) $producto->id_producto)>
                    {{ $producto->nombre }}{{ $producto->estado ? '' : ' (inactivo)' }}
                </option>
            @endforeach
        </select>
    </td>
    <td class="col-start-1 row-start-2 p-0 sm:table-cell sm:px-4 sm:py-3.5">
        <label for="cantidad_{{ $indice }}" class="sr-only">Cantidad</label>
        <input id="cantidad_{{ $indice }}" name="productos[{{ $indice }}][cantidad]" type="number" min="1" max="9999" step="1"
               inputmode="numeric" value="{{ $linea['cantidad'] }}" class="campo text-center" data-cantidad>
    </td>
    <td class="col-start-2 row-start-2 p-0 text-sm tabular-nums text-stone-500 before:content-['×_'] sm:table-cell sm:px-4 sm:py-3.5 sm:text-right sm:text-stone-600 sm:before:content-none" data-precio>—</td>
    <td class="col-start-3 row-start-2 p-0 text-right font-medium tabular-nums text-stone-900 sm:table-cell sm:px-4 sm:py-3.5" data-subtotal>—</td>
    <td class="col-start-3 row-start-1 p-0 text-right sm:table-cell sm:px-4 sm:py-3.5 sm:text-center">
        <button type="button" class="cursor-pointer rounded p-1 text-stone-400 hover:bg-red-50 hover:text-red-600" data-quitar-linea title="Quitar producto">
            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            <span class="sr-only">Quitar producto</span>
        </button>
    </td>
</tr>
