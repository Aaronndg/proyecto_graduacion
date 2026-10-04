@php
    $editando = $producto->exists;
    $disponible = (bool) old('estado', $producto->estado);
@endphp
<x-layouts.app :titulo="$editando ? 'Editar producto' : 'Nuevo producto'" :ruta="['Productos' => route('productos.index')]">
    @if ($editando)
        <x-slot:acciones>
            <x-menu-acciones etiqueta="Más acciones del producto">
                {{-- Si el producto ya está en pedidos, el servidor lo impide y sugiere desactivarlo. --}}
                <form method="POST" action="{{ route('productos.destroy', $producto) }}"
                      data-confirmar-titulo="¿Eliminar {{ $producto->nombre }}?" data-confirmar="Se quitará de su catálogo. Esta acción no se puede deshacer."
                      data-confirmar-accion="Eliminar producto" data-confirmar-peligro>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="menu-opcion menu-opcion-peligro"><x-icono nombre="cerrar" clase="size-4" /> Eliminar producto</button>
                </form>
            </x-menu-acciones>
        </x-slot:acciones>
    @endif

    <form method="POST" action="{{ $editando ? route('productos.update', $producto) : route('productos.store') }}"
          class="panel max-w-xl space-y-5 p-5 sm:p-6" novalidate data-envio-unico>
        @csrf
        @if ($editando) @method('PUT') @endif

        <x-campo nombre="nombre" etiqueta="Nombre" :valor="$producto->nombre" maxlength="100" requerido autofocus placeholder="Ej.: Pastel de chocolate" />

        <div class="sm:w-56">
            <label for="precio" class="etiqueta">Precio <span class="text-red-600" aria-hidden="true">*</span></label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-texto-2">Q</span>
                <input id="precio" name="precio" type="number" step="0.01" min="0.01" max="99999999.99" inputmode="decimal" required
                       value="{{ old('precio', $producto->precio) }}" @class(['campo pl-8 tabular-nums', 'campo-error' => $errors->has('precio')])
                       @error('precio') aria-invalid="true" aria-describedby="precio-error" @enderror>
            </div>
            @error('precio')<p id="precio-error" class="error-campo">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="descripcion" class="etiqueta">Descripción <span class="font-normal text-texto-2">(opcional)</span></label>
            <textarea id="descripcion" name="descripcion" rows="2" maxlength="255" placeholder="Tamaño, sabor o lo que ayude a distinguirlo"
                      @class(['campo', 'campo-error' => $errors->has('descripcion')])>{{ old('descripcion', $producto->descripcion) }}</textarea>
            @error('descripcion')<p class="error-campo">{{ $message }}</p>@enderror
        </div>

        {{-- Interruptor hecho con la casilla de siempre (role="switch"); el hidden envía 0 cuando está apagado --}}
        <div class="border-t border-borde pt-5">
            <input type="hidden" name="estado" value="0">
            <label class="flex cursor-pointer items-start justify-between gap-4">
                <span>
                    <span class="block text-sm font-medium">Disponible para nuevos pedidos</span>
                    <span class="ayuda block">Si lo apaga, deja de aparecer al crear pedidos, pero se conserva en los pedidos anteriores.</span>
                </span>
                <input type="checkbox" name="estado" value="1" role="switch" @checked($disponible)
                       class="relative mt-0.5 h-6 w-11 shrink-0 cursor-pointer appearance-none rounded-full bg-stone-300 transition-colors before:absolute before:top-0.5 before:left-0.5 before:size-5 before:rounded-full before:bg-white before:shadow-sm before:transition-transform checked:bg-marca checked:before:translate-x-5">
            </label>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-borde pt-5 sm:flex-row sm:justify-end">
            <a href="{{ route('productos.index') }}" class="btn btn-terciario">Cancelar</a>
            <button type="submit" class="btn btn-primario">{{ $editando ? 'Guardar cambios' : 'Registrar producto' }}</button>
        </div>
    </form>
</x-layouts.app>
