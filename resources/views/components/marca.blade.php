@props(['claro' => false, 'detalle' => null])
{{-- Logotipo: una caja (el pedido) con una marca de verificación (el seguimiento).
     «claro»: para la franja azul. «detalle»: línea pequeña bajo el nombre (p. ej. el negocio). --}}
<span {{ $attributes->class('inline-flex items-center gap-2.5') }}>
    <span @class(['flex size-9 shrink-0 items-center justify-center rounded-xl', 'bg-marca text-oro' => ! $claro, 'bg-white text-marca' => $claro])>
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 8.5 12 3 3 8.5v7L12 21l9-5.5v-7Z" />
            <path d="m3 8.5 9 5.5 9-5.5M12 14v7" opacity=".55" />
            <path d="m8.5 10.8 2.2 2.1 4.8-4.6" />
        </svg>
    </span>
    <span class="leading-none">
        <span @class(['block font-display text-[19px] font-semibold', 'text-white' => $claro, 'text-texto' => ! $claro])>Pedidos Jutiapa</span>
        @if ($detalle)<span @class(['mt-1 block text-[11.5px] font-bold', 'text-stone-300' => $claro, 'text-texto-2' => ! $claro])>{{ $detalle }}</span>@endif
    </span>
</span>
