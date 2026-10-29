@props([
    'nombre',
    'etiqueta',
    'tipo' => 'text',
    'valor' => null,
    'requerido' => false,
    'ayuda' => null,
    'bolsa' => 'default',
])
@php
    // Admite nombres de arreglo, p. ej. nuevo_cliente[nombre] → nuevo_cliente.nombre
    $clave = str_replace(['[', ']'], ['.', ''], $nombre);
    $error = $errors->getBag($bolsa)->first($clave);
    $id = $attributes->get('id', $nombre);
@endphp
{{-- Campo de formulario con etiqueta, marca de obligatorio y mensaje de validación (5.5.4). --}}
<div>
    <label for="{{ $id }}" class="etiqueta">
        {{ $etiqueta }}
        @if ($requerido)<span class="text-red-600" aria-hidden="true">*</span>@endif
    </label>
    @if ($tipo === 'password')<div class="relative">@endif
    <input
        id="{{ $id }}"
        name="{{ $nombre }}"
        type="{{ $tipo }}"
        @if ($tipo !== 'password') value="{{ old($clave, $valor) }}" @endif
        @required($requerido)
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->except('id')->class(['campo', 'campo-error' => $error, 'pr-12' => $tipo === 'password']) }}
    >
    @if ($tipo === 'password')
        {{-- Mostrar u ocultar lo escrito (resources/js/app.js) --}}
        <button type="button" data-ver-contrasena="{{ $id }}" aria-controls="{{ $id }}" aria-pressed="false"
                aria-label="Mostrar contraseña" title="Mostrar contraseña"
                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-texto-2 transition-colors hover:text-marca focus-visible:text-marca focus-visible:outline-none">
            <x-icono nombre="ojo" data-icono-mostrar />
            <x-icono nombre="ojo-tachado" class="hidden" data-icono-ocultar />
        </button>
    </div>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="error-campo">{{ $error }}</p>
    @elseif ($ayuda)
        <p class="ayuda">{{ $ayuda }}</p>
    @endif
</div>
