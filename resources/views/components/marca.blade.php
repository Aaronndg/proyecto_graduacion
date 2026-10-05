@props(['claro' => false, 'detalle' => null])
{{-- Logotipo NEXO: el símbolo «N» (siempre sobre blanco, como en el logo original) y el nombre.
     «claro»: para la franja azul (nombre en blanco). «detalle»: línea pequeña bajo el nombre (p. ej. el negocio). --}}
<span {{ $attributes->class('inline-flex items-center gap-2.5') }}>
    <span @class(['flex size-9 shrink-0 items-center justify-center rounded-xl bg-white', 'shadow-[0_0_0_1px_var(--color-borde)]' => ! $claro])>
        <img src="{{ asset('img/nexo-marca.png') }}" alt="" class="h-5 w-auto">
    </span>
    <span class="leading-none">
        <img src="{{ asset($claro ? 'img/nexo-texto-blanco.png' : 'img/nexo-texto.png') }}" alt="NEXO" class="block h-[15px] w-auto">
        @if ($detalle)<span @class(['mt-1.5 block text-[11.5px] font-semibold', 'text-stone-300' => $claro, 'text-texto-2' => ! $claro])>{{ $detalle }}</span>@endif
    </span>
</span>
