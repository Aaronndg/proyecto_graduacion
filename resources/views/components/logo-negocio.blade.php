@props(['negocio', 'tamano' => 'md'])
{{-- Logo del negocio; si no subió uno, sus iniciales en un círculo de color. --}}
@php $medida = ['sm' => 'size-8', 'md' => 'size-10', 'lg' => 'size-14'][$tamano]; @endphp
@if ($url = $negocio->urlLogo())
    <img src="{{ $url }}" alt="{{ $negocio->nombreNegocio() }}" {{ $attributes->class(['shrink-0 rounded-xl bg-white object-contain shadow-[0_0_0_1px_var(--color-borde)]', $medida]) }}>
@else
    <x-avatar :nombre="$negocio->nombreNegocio()" :tamano="$tamano === 'lg' ? 'lg' : ($tamano === 'sm' ? 'sm' : 'md')" {{ $attributes }} />
@endif
