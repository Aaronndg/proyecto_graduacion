@props(['titulo', 'antetitulo' => null])
{{-- Banner de bienvenida (estilo «Oro tejido»): saludo, texto y acciones sobre azul noche.
     Lleva el <h1> de la pantalla, por eso se usa con <x-layouts.app :encabezado="false">. --}}
<section {{ $attributes->class('relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-marca to-[#1D3D63] px-5 py-6 text-white shadow-suave sm:px-8 sm:py-7') }}>
    <span class="pointer-events-none absolute -top-20 -right-16 size-60 rounded-full bg-oro/15" aria-hidden="true"></span>
    <div class="relative">
        @if ($antetitulo)<p class="text-sm font-bold text-stone-300">{{ $antetitulo }}</p>@endif
        <h1 class="mt-1 font-display text-[28px] leading-tight font-semibold text-balance sm:text-[34px]">{{ $titulo }}</h1>
        <div class="mt-2 text-[17px] text-stone-200">{{ $slot }}</div>
        @isset($acciones)
            <div class="mt-5 flex flex-wrap gap-2.5">{{ $acciones }}</div>
        @endisset
    </div>
</section>
