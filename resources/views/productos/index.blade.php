<x-layouts.app titulo="Gestión de productos">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <form method="GET" class="flex flex-1 flex-col gap-2 sm:flex-row" role="search">
            <div class="flex-1 sm:max-w-sm">
                <label for="buscar" class="sr-only">Buscar producto</label>
                <input id="buscar" name="buscar" value="{{ $buscar }}" placeholder="Buscar por nombre o descripción" class="campo">
            </div>
            <div>
                <label for="estado" class="sr-only">Estado</label>
                <select id="estado" name="estado" class="campo">
                    <option value="">Todos</option>
                    <option value="activos" @selected($estado === 'activos')>Activos</option>
                    <option value="inactivos" @selected($estado === 'inactivos')>Inactivos</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secundario"><x-icono nombre="buscar" clase="size-4" /> Buscar</button>
        </form>
        <a href="{{ route('productos.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Nuevo producto</a>
    </div>

    <div class="tarjeta overflow-hidden">
        @if ($productos->isEmpty())
            <x-vacio icono="producto" :titulo="$buscar || $estado ? 'No se encontraron productos' : 'Aún no tiene productos registrados'"
                     :texto="$buscar || $estado ? null : 'Registre su catálogo para agregar productos a los pedidos.'">
                @unless ($buscar || $estado)<a href="{{ route('productos.create') }}" class="btn btn-primario">Registrar el primer producto</a>@endunless
            </x-vacio>
        @else
            <div class="overflow-x-auto">
                <table class="tabla">
                    <thead>
                        <tr><th>Producto</th><th class="text-right">Precio</th><th>Estado</th><th class="text-right">Acciones</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($productos as $producto)
                            <tr @class(['opacity-60' => ! $producto->estado])>
                                <td>
                                    <p class="font-medium text-slate-900">{{ $producto->nombre }}</p>
                                    @if ($producto->descripcion)<p class="max-w-md truncate text-xs text-slate-500">{{ $producto->descripcion }}</p>@endif
                                </td>
                                <td class="text-right"><x-moneda :valor="$producto->precio" /></td>
                                <td>
                                    <span @class(['insignia', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $producto->estado, 'bg-slate-100 text-slate-600 ring-slate-500/20' => ! $producto->estado])>
                                        <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
                                        {{ $producto->estado ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex justify-end">
                                        <a href="{{ route('productos.edit', $producto) }}" class="btn btn-secundario px-3 py-1.5"><x-icono nombre="editar" clase="size-4" /> Editar</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-4">{{ $productos->links() }}</div>
</x-layouts.app>
