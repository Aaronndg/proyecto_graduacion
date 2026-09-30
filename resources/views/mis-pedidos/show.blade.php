<x-layouts.app :titulo="$pedido->estado->mensajeCliente()"
                :subtitulo="'Pedido #'.$pedido->numero().' en '.($pedido->emprendedor->negocio ?? $pedido->emprendedor->nombre).' · '.$pedido->fecha->translatedFormat('d \d\e F, H:i')">
    <x-slot:acciones>
        <a href="{{ route('panel') }}" class="btn btn-secundario">&larr; Mis pedidos</a>
    </x-slot:acciones>

    <section class="tarjeta mb-6 p-5 sm:p-8">
        <x-progreso-pedido :pedido="$pedido" />
    </section>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <section class="tarjeta overflow-hidden lg:col-span-3">
            <h2 class="px-5 pt-5 pb-3 font-semibold text-stone-900">Lo que pidió</h2>
            <ul class="divide-y divide-stone-100">
                @foreach ($pedido->detalles as $detalle)
                    <li class="flex items-center justify-between gap-4 px-5 py-3.5">
                        <span class="text-stone-800">
                            <span class="mr-2 inline-flex min-w-7 justify-center rounded-lg bg-stone-100 px-1.5 py-0.5 text-sm font-semibold text-stone-600">{{ $detalle->cantidad }}×</span>
                            {{ $detalle->producto->nombre }}
                        </span>
                        <x-moneda :valor="$detalle->subtotal" class="text-stone-700" />
                    </li>
                @endforeach
            </ul>
            <div class="flex items-center justify-between bg-stone-50 px-5 py-4">
                <span class="font-semibold text-stone-700">Total</span>
                <x-moneda :valor="$pedido->total" class="text-lg font-bold text-stone-900" />
            </div>
        </section>

        <section class="tarjeta lg:col-span-2">
            <h2 class="px-5 pt-5 pb-1 font-semibold text-stone-900">Novedades</h2>
            <ol class="relative space-y-5 p-5">
                @foreach ($pedido->historial->reverse() as $registro)
                    <li class="flex gap-3">
                        <span @class(['mt-1.5 size-2.5 shrink-0 rounded-full', 'bg-marca-500 ring-4 ring-marca-100' => $loop->first, 'bg-stone-300' => ! $loop->first]) aria-hidden="true"></span>
                        <div>
                            <p class="font-medium text-stone-900">{{ $registro->observacion ?: $registro->estado->nombre }}</p>
                            <p class="text-xs text-stone-500">{{ $registro->fecha_hora->translatedFormat('d M, H:i') }} · {{ $registro->estado->nombre }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
</x-layouts.app>
