@php
    $editando = $cliente->exists;
    $volver = $editando ? route('clientes.show', $cliente) : route('clientes.index');
    $ruta = $editando ? ['Clientes' => route('clientes.index'), $cliente->nombre => route('clientes.show', $cliente)] : ['Clientes' => route('clientes.index')];
@endphp
<x-layouts.app :titulo="$editando ? 'Editar cliente' : 'Nuevo cliente'" :ruta="$ruta">
    <form method="POST" action="{{ $editando ? route('clientes.update', $cliente) : route('clientes.store') }}"
          class="panel max-w-xl space-y-5 p-5 sm:p-6" novalidate data-envio-unico>
        @csrf
        @if ($editando) @method('PUT') @endif

        <x-campo nombre="nombre" etiqueta="Nombre" :valor="$cliente->nombre" autocomplete="off" maxlength="100" requerido autofocus />
        <div class="grid gap-5 sm:grid-cols-2">
            <x-campo nombre="telefono" etiqueta="Teléfono" tipo="tel" :valor="$cliente->telefono" placeholder="5555-5555" inputmode="tel" maxlength="20"
                     ayuda="Sirve para enviarle la invitación por WhatsApp." />
            <x-campo nombre="correo" etiqueta="Correo electrónico" tipo="email" :valor="$cliente->correo" maxlength="150" />
        </div>
        <x-campo nombre="direccion" etiqueta="Dirección" :valor="$cliente->direccion" placeholder="Barrio, zona o referencia en Jutiapa" maxlength="255" />

        <div class="flex flex-col-reverse gap-2 border-t border-borde pt-5 sm:flex-row sm:justify-end">
            <a href="{{ $volver }}" class="btn btn-terciario">Cancelar</a>
            <button type="submit" class="btn btn-primario">{{ $editando ? 'Guardar cambios' : 'Registrar cliente' }}</button>
        </div>
    </form>
</x-layouts.app>
