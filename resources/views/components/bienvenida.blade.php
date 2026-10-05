@props(['titulo', 'antetitulo' => null])
{{-- Banner de bienvenida de NEXO: la imagen de marca a la derecha y el saludo sobre el azul a la izquierda.
     Lleva el <h1> de la pantalla, por eso se usa con <x-layouts.app :encabezado="false">.
     Ranuras: el texto (slot), «acciones» (botones) y «pie» (una línea de contexto, p. ej. ventas). --}}
<section {{ $attributes->class('relative isolate mb-6 overflow-hidden rounded-2xl bg-marca-700 text-white shadow-suave') }}>
    <picture>
        <source media="(min-width: 1024px)" srcset="{{ asset('img/banner-nexo.jpg') }}">
        <img src="{{ asset('img/banner-nexo-movil.jpg') }}" alt="" class="absolute inset-0 -z-20 size-full object-cover object-right lg:object-[right_center]">
    </picture>
    {{-- Velo: asegura la lectura del texto en cualquier tamaño de pantalla --}}
    <span class="absolute inset-0 -z-10 bg-gradient-to-r from-marca-700 via-marca-700/85 to-marca-700/30 lg:via-marca-700/70 lg:to-transparent" aria-hidden="true"></span>

    <div class="max-w-xl px-5 py-7 sm:px-8 sm:py-9 lg:py-10">
        @if ($antetitulo)<p class="text-sm font-semibold text-stone-300">{{ $antetitulo }}</p>@endif
        <h1 class="mt-1 text-[28px] leading-tight font-bold text-balance sm:text-[36px]">{{ $titulo }}</h1>
        <div class="mt-2 text-[17px] text-stone-200">{{ $slot }}</div>
        @isset($acciones)
            <div class="mt-6 flex flex-wrap gap-2.5">{{ $acciones }}</div>
        @endisset
        @isset($pie)
            <div class="mt-5">{{ $pie }}</div>
        @endisset
    </div>
</section>
