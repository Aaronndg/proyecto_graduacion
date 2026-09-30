@props(['valor' => null, 'placeholder' => 'Buscar…'])
{{-- Campo de búsqueda con icono. --}}
<div class="relative flex-1">
    <label for="buscar" class="sr-only">Buscar</label>
    <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-stone-400">
        <x-icono nombre="buscar" clase="size-4" />
    </span>
    <input id="buscar" name="buscar" type="search" value="{{ $valor }}" placeholder="{{ $placeholder }}" {{ $attributes->class('campo pl-10') }}>
</div>
