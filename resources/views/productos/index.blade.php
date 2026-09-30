<x-layouts.app titulo="Productos" subtitulo="Su catálogo para registrar pedidos.">
    <x-slot:acciones>
        <a href="{{ route('productos.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Nuevo producto</a>
    </x-slot:acciones>

    <div class="tarjeta overflow-hidden">
        <form method="GET" class="flex flex-col gap-2 border-b border-stone-100 p-4 sm:flex-row" role="search">
            <x-campo-busqueda :valor="$buscar" placeholder="Buscar producto" />
            <label for="estado" class="sr-only">Mostrar</label>
            <select id="estado" name="estado" class="campo sm:w-44" onchange="this.form.submit()">
                <option value="">Todos</option>
                <option value="activos" @selected($estado === 'activos')>Solo activos</option>
                <option value="inactivos" @selected($estado === 'inactivos')>Solo inactivos</option>
            </select>
            <button type="submit" class="btn btn-secundario">Buscar</button>
        </form>

        @if ($productos->isEmpty())
            <x-vacio icono="producto" :titulo="$buscar || $estado ? 'No encontramos productos' : 'Aún no tiene productos'"
                     :texto="$buscar || $estado ? null : 'Agregue los productos que vende para poder registrar pedidos.'">
                @unless ($buscar || $estado)<a href="{{ route('productos.create') }}" class="btn btn-primario">Agregar el primer producto</a>@endunless
            </x-vacio>
        @else
            <div class="overflow-x-auto">
                <table class="tabla">
                    <thead>
                        <tr><th>Producto</th><th class="text-right">Precio</th><th>Disponible</th><th><span class="sr-only">Acciones</span></th></tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($productos as $producto)
                            <tr @class(['text-stone-400' => ! $producto->estado])>
                                <td>
                                    <p @class(['font-medium', 'text-stone-900' => $producto->estado, 'text-stone-500' => ! $producto->estado])>{{ $producto->nombre }}</p>
                                    @if ($producto->descripcion)<p class="max-w-md truncate text-xs text-stone-500">{{ $producto->descripcion }}</p>@endif
                                </td>
                                <td class="text-right font-medium"><x-moneda :valor="$producto->precio" /></td>
                                <td>
                                    <span @class(['insignia', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $producto->estado, 'bg-stone-100 text-stone-500 ring-stone-500/20' => ! $producto->estado])>
                                        {{ $producto->estado ? 'Sí' : 'No' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('productos.edit', $producto) }}" class="btn btn-secundario px-3 py-1.5"><x-icono nombre="editar" clase="size-4" /> Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($productos->hasPages())<div class="border-t border-stone-100 px-5 py-3">{{ $productos->links() }}</div>@endif
        @endif
    </div>
</x-layouts.app>
