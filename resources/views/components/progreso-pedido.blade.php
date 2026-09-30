@props(['pedido'])
@php
    $pasos = \App\Models\EstadoPedido::whereIn('id_estado', \App\Models\EstadoPedido::FLUJO)->orderBy('orden')->get();
    $cancelado = $pedido->id_estado === \App\Models\EstadoPedido::CANCELADO;
    // Fecha en que el pedido alcanzó cada estado, según el historial.
    $fechas = $pedido->historial->groupBy('id_estado')->map(fn ($registros) => $registros->last()->fecha_hora);
    $ordenActual = $cancelado
        ? $pedido->historial->where('id_estado', '!=', \App\Models\EstadoPedido::CANCELADO)->map(fn ($r) => $r->estado->orden)->max()
        : $pedido->estado->orden;
@endphp
{{-- Barra de progreso del pedido: identifica visualmente y con texto la etapa actual (5.5.4). --}}
<div {{ $attributes }}>
    @if ($cancelado)
        <div role="status" class="mb-4 flex items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            <x-icono nombre="alerta" clase="size-5 shrink-0 text-slate-500" />
            <span><strong>Pedido cancelado</strong> el {{ $fechas->get(\App\Models\EstadoPedido::CANCELADO)?->format('d/m/Y \a \l\a\s H:i') }}.</span>
        </div>
    @endif

    <ol class="grid grid-cols-4 gap-2" aria-label="Progreso del pedido">
        @foreach ($pasos as $paso)
            @php
                $completo = $paso->orden <= $ordenActual;
                $actual = ! $cancelado && $paso->id_estado === $pedido->id_estado;
            @endphp
            <li class="flex flex-col items-center text-center" @if ($actual) aria-current="step" @endif>
                <div class="flex w-full items-center">
                    <span @class(['h-0.5 flex-1', 'invisible' => $loop->first, 'bg-marca-500' => $completo && ! $cancelado, 'bg-slate-200' => ! $completo || $cancelado])></span>
                    <span @class([
                        'flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                        'bg-marca-600 text-white ring-4 ring-marca-100' => $actual,
                        'bg-marca-500 text-white' => $completo && ! $actual && ! $cancelado,
                        'bg-slate-400 text-white' => $completo && $cancelado,
                        'border-2 border-slate-200 bg-white text-slate-400' => ! $completo,
                    ])>
                        @if ($completo && ! $actual)
                            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </span>
                    <span @class(['h-0.5 flex-1', 'invisible' => $loop->last, 'bg-marca-500' => $paso->orden < $ordenActual && ! $cancelado, 'bg-slate-200' => $paso->orden >= $ordenActual || $cancelado])></span>
                </div>
                <span @class(['mt-2 text-xs font-semibold sm:text-sm', 'text-marca-700' => $actual, 'text-slate-700' => $completo && ! $actual, 'text-slate-400' => ! $completo])>
                    {{ $paso->nombre }}
                </span>
                <span class="text-[11px] text-slate-500 sm:text-xs">{{ $completo ? $fechas->get($paso->id_estado)?->format('d/m H:i') : '' }}</span>
            </li>
        @endforeach
    </ol>
</div>
