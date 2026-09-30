@php $editando = $producto->exists; @endphp
<x-layouts.app :titulo="$editando ? 'Editar producto' : 'Nuevo producto'">

    <x-slot:acciones>
        <a href="{{ route('productos.index') }}" class="btn btn-secundario">&larr; Productos</a>
    </x-slot:acciones>
    <div class="max-w-2xl">

        <form method="POST" action="{{ $editando ? route('productos.update', $producto) : route('productos.store') }}" class="tarjeta space-y-5 p-6" novalidate data-envio-unico>
            @csrf
            @if ($editando) @method('PUT') @endif

            <x-campo nombre="nombre" etiqueta="Nombre" :valor="$producto->nombre" maxlength="100" requerido autofocus />

            <div>
                <label for="descripcion" class="etiqueta">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3" maxlength="255" @class(['campo', 'campo-error' => $errors->has('descripcion')])>{{ old('descripcion', $producto->descripcion) }}</textarea>
                @error('descripcion')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="precio" class="etiqueta">Precio <span class="text-red-600" aria-hidden="true">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-sm text-stone-500">Q</span>
                        <input id="precio" name="precio" type="number" step="0.01" min="0.01" max="99999999.99" inputmode="decimal" required
                               value="{{ old('precio', $producto->precio) }}" @class(['campo pl-8', 'campo-error' => $errors->has('precio')])>
                    </div>
                    @error('precio')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <span class="etiqueta">Estado</span>
                    <label class="flex h-[38px] items-center gap-3 text-sm text-stone-700">
                        <input type="hidden" name="estado" value="0">
                        <input type="checkbox" name="estado" value="1" class="size-4 accent-marca-600" @checked(old('estado', $producto->estado))>
                        Activo (disponible para nuevos pedidos)
                    </label>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-stone-200 pt-5">
                <a href="{{ route('productos.index') }}" class="btn btn-secundario">Cancelar</a>
                <button type="submit" class="btn btn-primario">{{ $editando ? 'Guardar cambios' : 'Registrar producto' }}</button>
            </div>
        </form>

        @if ($editando)
            <form method="POST" action="{{ route('productos.destroy', $producto) }}" class="mt-4 text-right"
                  data-confirmar="¿Eliminar el producto {{ $producto->nombre }}? Esta acción no se puede deshacer.">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm font-medium text-red-600 hover:underline">Eliminar producto</button>
            </form>
        @endif
    </div>
</x-layouts.app>
