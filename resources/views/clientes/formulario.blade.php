@php $editando = $cliente->exists; @endphp
<x-layouts.app :titulo="$editando ? 'Editar cliente' : 'Nuevo cliente'">

    <x-slot:acciones>
        <a href="{{ $editando ? route('clientes.show', $cliente) : route('clientes.index') }}" class="btn btn-secundario">&larr; Volver</a>
    </x-slot:acciones>
    <div class="max-w-2xl">

        <form method="POST" action="{{ $editando ? route('clientes.update', $cliente) : route('clientes.store') }}" class="tarjeta space-y-5 p-6" novalidate data-envio-unico>
            @csrf
            @if ($editando) @method('PUT') @endif

            <x-campo nombre="nombre" etiqueta="Nombre" :valor="$cliente->nombre" autocomplete="off" requerido autofocus />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-campo nombre="telefono" etiqueta="Teléfono" tipo="tel" :valor="$cliente->telefono" placeholder="5555-5555" inputmode="tel" />
                <x-campo nombre="correo" etiqueta="Correo electrónico" tipo="email" :valor="$cliente->correo" />
            </div>
            <x-campo nombre="direccion" etiqueta="Dirección" :valor="$cliente->direccion" placeholder="Barrio, zona o referencia en Jutiapa" />

            <div class="flex justify-end gap-3 border-t border-stone-200 pt-5">
                <a href="{{ $editando ? route('clientes.show', $cliente) : route('clientes.index') }}" class="btn btn-secundario">Cancelar</a>
                <button type="submit" class="btn btn-primario">{{ $editando ? 'Guardar cambios' : 'Registrar cliente' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>
