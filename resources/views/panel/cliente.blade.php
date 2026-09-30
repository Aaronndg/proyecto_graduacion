<x-layouts.app titulo="Mis pedidos" :subtitulo="'Hola, '.strtok(auth()->user()->nombre, ' ').'. Aquí puede ver cómo van sus pedidos.'">
    @if ($pedidos->isEmpty())
        {{-- Primera vez: explicar en 3 pasos cómo ver sus pedidos. --}}
        <section class="tarjeta mx-auto max-w-2xl p-6 sm:p-10">
            <div class="text-center">
                <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-marca-50 text-marca-600">
                    <x-icono nombre="pedido" clase="size-7" />
                </span>
                <h2 class="mt-4 text-xl font-bold text-stone-900">Conecte su cuenta con el negocio</h2>
                <p class="mt-1 text-stone-500">Así podrá ver sus pedidos. Solo toma un momento.</p>
            </div>

            <ol class="mt-8 grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['Pida su código', 'El negocio donde compró se lo envía, por ejemplo por WhatsApp.'],
                    ['Escríbalo abajo', new \Illuminate\Support\HtmlString('Son 8 letras y números, como <span class="whitespace-nowrap font-mono">K7QM-4XPA</span>.')],
                    ['¡Listo!', 'Verá sus pedidos y en qué etapa van.'],
                ] as $i => [$paso, $detalle])
                    <li class="rounded-2xl bg-stone-50 p-4 text-center sm:text-left">
                        <span class="inline-flex size-7 items-center justify-center rounded-full bg-marca-600 text-sm font-bold text-white">{{ $i + 1 }}</span>
                        <p class="mt-2 font-semibold text-stone-900">{{ $paso }}</p>
                        <p class="mt-0.5 text-sm text-stone-500">{{ $detalle }}</p>
                    </li>
                @endforeach
            </ol>

            <x-formulario-codigo grande class="mt-8" />
        </section>
    @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($pedidos as $pedido)
                <a href="{{ route('mis-pedidos.show', $pedido) }}"
                   class="tarjeta group flex flex-col gap-4 p-5 transition hover:-translate-y-0.5 hover:border-marca-200 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-stone-500">{{ $pedido->emprendedor->negocio ?? $pedido->emprendedor->nombre }}</p>
                            <p class="text-lg font-bold text-stone-900">{{ $pedido->estado->mensajeCliente() }}</p>
                        </div>
                        <x-estado-pedido :estado="$pedido->estado" />
                    </div>
                    <div class="flex items-end justify-between border-t border-stone-100 pt-4 text-sm">
                        <span class="text-stone-500">Pedido #{{ $pedido->numero() }} · {{ $pedido->fecha->translatedFormat('d M Y') }}</span>
                        <span class="flex items-center gap-2 font-semibold text-stone-900">
                            <x-moneda :valor="$pedido->total" />
                            <span class="text-marca-600 transition group-hover:translate-x-0.5" aria-hidden="true">&rarr;</span>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <details class="tarjeta mt-6 p-5" @if ($errors->has('codigo')) open @endif>
            <summary class="cursor-pointer font-medium text-stone-700 marker:text-stone-400">¿Compró en otro negocio? Agregue su código</summary>
            <p class="mt-2 mb-4 text-sm text-stone-500">Escriba el código de cliente que le envió ese negocio.</p>
            <x-formulario-codigo class="max-w-md" />
        </details>
    @endif
</x-layouts.app>
