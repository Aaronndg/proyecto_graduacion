@props(['titulo', 'antetitulo' => null])
{{-- Banner de bienvenida de NEXO (Bento negro y oro): la imagen de la caja dorada a la derecha (arriba en el teléfono)
     y el saludo sobre negro. Lleva el <h1> de la pantalla, por eso se usa con <x-layouts.app :encabezado="false">.
     Ranuras: el texto (slot), «acciones» (botones) y «pie» (una línea de contexto o cifras). --}}
<section {{ $attributes->class('relative isolate mb-4 overflow-hidden rounded-2xl bg-[#0B0C0F] text-white') }}
         style="box-shadow: inset 0 1px 0 rgb(255 255 255 / .08), 0 0 0 1px rgb(224 176 79 / .18), 0 3px 0 rgb(0 0 0 / .7), 0 30px 60px -24px rgb(76 141 255 / .35)">
    {{-- Teléfono: la caja arriba; computadora: la imagen completa a la derecha --}}
    <img src="{{ asset('img/banner-nexo-movil.jpg') }}" alt="" class="absolute inset-x-0 top-0 -z-20 h-44 w-full object-cover object-center lg:hidden">
    <img src="{{ asset('img/banner-nexo.jpg') }}" alt="" class="absolute inset-y-0 right-0 -z-20 hidden h-full w-auto max-w-none lg:block">
    {{-- Velo: asegura la lectura del texto --}}
    <span class="absolute inset-0 -z-10 bg-linear-to-t from-[#0B0C0F] from-45% via-[#0B0C0F]/70 to-transparent lg:bg-linear-to-r lg:from-[#0B0C0F] lg:from-25% lg:via-[#0B0C0F]/75 lg:via-45% lg:to-transparent lg:to-65%" aria-hidden="true"></span>

    <div class="max-w-xl px-5 pt-36 pb-6 sm:px-8 lg:py-10">
        @if ($antetitulo)<p class="text-sm font-semibold text-champan">{{ $antetitulo }}</p>@endif
        <h1 class="mt-1 font-display text-[28px] leading-tight font-semibold text-balance sm:text-[38px]">{{ $titulo }}</h1>
        <div class="mt-2 text-[16.5px] text-stone-700">{{ $slot }}</div>
        @isset($acciones)
            <div class="mt-6 flex flex-wrap gap-2.5">{{ $acciones }}</div>
        @endisset
        @isset($pie)
            <div class="mt-5">{{ $pie }}</div>
        @endisset
    </div>
</section>
