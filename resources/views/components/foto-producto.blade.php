@props(['producto', 'icono' => 'size-8'])
{{-- Foto del producto; si no tiene, una caja ilustrada en su lugar (el contenedor define el tamaño). --}}
@if ($url = $producto?->urlImagen())
    <img src="{{ $url }}" alt="{{ $producto->nombre }}" loading="lazy" {{ $attributes->class('size-full object-cover') }}>
@else
    <span {{ $attributes->class('flex size-full items-center justify-center bg-superficie-2 text-stone-400') }} aria-hidden="true">
        <svg class="{{ $icono }}" viewBox="0 0 24 24"><path d="M21 8 12 3 3 8v8l9 5 9-5V8Z" fill="currentColor" opacity=".22"/><path d="M21 8 12 3 3 8v8l9 5 9-5V8Zm-18 0 9 5 9-5M12 13v8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
    </span>
@endif
