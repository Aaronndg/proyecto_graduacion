@props(['estado', 'barra' => false])
@php
    use App\Models\EstadoPedido;

    // Colores semánticos suaves: Nuevo informa, En proceso avisa, Listo pide entregar,
    // Entregado se aquieta (terminado) y Cancelado usa terracota. Contraste AA en todos.
    [$chip, $relleno] = match ($estado->id_estado) {
        EstadoPedido::NUEVO => ['bg-sky-50 text-sky-700', 'bg-sky-500'],
        EstadoPedido::EN_PROCESO => ['bg-amber-50 text-amber-700', 'bg-amber-500'],
        EstadoPedido::LISTO => ['bg-emerald-50 text-emerald-700', 'bg-emerald-500'],
        EstadoPedido::ENTREGADO => ['bg-stone-100 text-stone-600', 'bg-stone-400'],
        EstadoPedido::CANCELADO => ['bg-red-50 text-red-700', 'bg-red-400'],
        default => ['bg-stone-100 text-stone-600', 'bg-stone-400'],
    };
@endphp
@if ($barra)
    {{-- Relleno de barra (reportes): mismo tono que la etiqueta del estado. --}}
    <div {{ $attributes->class(['h-full rounded-full', $relleno]) }}></div>
@else
    {{-- El estado se identifica con texto, no solo con color (5.5.4). Entregado lleva ✓ en lugar del punto. --}}
    <span {{ $attributes->class(['insignia', $chip]) }}>
        @if ($estado->id_estado === EstadoPedido::ENTREGADO)
            <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
        @else
            <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
        @endif
        {{ $estado->nombre }}
    </span>
@endif
