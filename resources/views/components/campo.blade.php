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
    $error = $errors->getBag($bolsa)->first($nombre);
    $id = $attributes->get('id', $nombre);
@endphp
{{-- Campo de formulario con etiqueta, marca de obligatorio y mensaje de validación (5.5.4). --}}
<div>
    <label for="{{ $id }}" class="etiqueta">
        {{ $etiqueta }}
        @if ($requerido)<span class="text-red-600" aria-hidden="true">*</span>@endif
    </label>
    <input
        id="{{ $id }}"
        name="{{ $nombre }}"
        type="{{ $tipo }}"
        @if ($tipo !== 'password') value="{{ old($nombre, $valor) }}" @endif
        @required($requerido)
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->except('id')->class(['campo', 'campo-error' => $error]) }}
    >
    @if ($error)
        <p id="{{ $id }}-error" class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @elseif ($ayuda)
        <p class="mt-1 text-xs text-slate-500">{{ $ayuda }}</p>
    @endif
</div>
