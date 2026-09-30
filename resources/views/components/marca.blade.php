@props(['claro' => false])
{{-- Logotipo del sistema: una caja (el pedido) con una marca de verificación (el seguimiento). --}}
<div {{ $attributes->class('flex items-center gap-3') }}>
    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-marca-500 to-marca-700 text-white shadow-md shadow-marca-900/20">
        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 8.5 12 3 3 8.5v7L12 21l9-5.5v-7Z" />
            <path d="m3 8.5 9 5.5 9-5.5M12 14v7" opacity=".55" />
            <path d="m8.5 10.8 2.2 2.1 4.8-4.6" />
        </svg>
    </span>
    <span class="leading-tight">
        <span @class(['block text-[15px] font-bold tracking-tight', 'text-white' => $claro, 'text-stone-900' => ! $claro])>Pedidos Jutiapa</span>
        <span @class(['block text-xs', 'text-marca-100/80' => $claro, 'text-stone-500' => ! $claro])>Gestión y seguimiento</span>
    </span>
</div>
