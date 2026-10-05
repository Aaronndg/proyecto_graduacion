@php $negocio = $pedido->emprendedor->negocio ?? $pedido->emprendedor->nombre; @endphp
<x-layouts.app :titulo="$pedido->estado->mensajeCliente()"
                :subtitulo="$negocio.' · '.$pedido->fecha->translatedFormat('j \d\e F, H:i')"
                :ruta="['Mis pedidos' => route('panel'), '#'.$pedido->numero() => route('mis-pedidos.show', $pedido)]">
    <div class="space-y-6">
        <section class="panel p-5" aria-labelledby="titulo-estado">
            <h2 id="titulo-estado" class="sr-only">En qué etapa va su pedido</h2>
            <div class="mb-5 flex flex-wrap items-center gap-2"><x-estado-pedido :estado="$pedido->estado" /><x-entrega :pedido="$pedido" /></div>
            <x-progreso-pedido :pedido="$pedido" />
        </section>

        <section class="panel overflow-hidden" aria-labelledby="titulo-productos">
            <h2 id="titulo-productos" class="titulo-seccion px-5 pt-4 pb-2">Lo que pidió</h2>
            <ul>
                @foreach ($pedido->detalles as $detalle)
                    <li class="flex items-center gap-3 border-b border-borde px-5 py-3 last:border-b-0">
                        <span class="size-12 shrink-0 overflow-hidden rounded-xl"><x-foto-producto :producto="$detalle->producto" icono="size-6" /></span>
                        <span class="min-w-0 flex-1">
                            <span class="mr-1 tabular-nums text-texto-2">{{ $detalle->cantidad }} ×</span>
                            <span class="font-medium">{{ $detalle->producto->nombre }}</span>
                            <span class="meta block">Q {{ number_format((float) $detalle->precio_unitario, 2) }} c/u</span>
                        </span>
                        <x-moneda :valor="$detalle->subtotal" />
                    </li>
                @endforeach
            </ul>
            <div class="flex items-baseline justify-between border-t border-borde bg-superficie-2 px-5 py-3">
                <span class="font-medium">Total</span>
                <x-moneda :valor="$pedido->total" class="text-lg font-semibold" />
            </div>
        </section>

        {{-- Novedades: lo que el negocio fue anotando, la más reciente arriba --}}
        <section aria-labelledby="titulo-novedades">
            <h2 id="titulo-novedades" class="titulo-seccion mb-3">Novedades</h2>
            <ol class="space-y-4 border-l border-borde pl-4">
                @foreach ($pedido->historial->reverse() as $registro)
                    <li class="relative">
                        <span @class(['absolute top-1.5 -left-[21px] size-2.5 rounded-full ring-4 ring-fondo', 'bg-marca' => $loop->first, 'bg-stone-300' => ! $loop->first]) aria-hidden="true"></span>
                        <p class="text-sm"><span class="font-medium">{{ $registro->estado->nombre }}</span> · <span class="text-texto-2">{{ $registro->fecha_hora->translatedFormat('j M, H:i') }}</span></p>
                        @if ($registro->observacion)<p class="text-sm">{{ $registro->observacion }}</p>@endif
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
</x-layouts.app>
