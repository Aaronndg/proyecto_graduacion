<x-layouts.app titulo="Seguimiento de pedidos">
    <p class="mb-5 text-slate-500">Pedidos activos organizados por etapa. Avance cada pedido cuando cambie su situación; el cambio queda registrado en su historial.</p>

    <div class="grid gap-5 lg:grid-cols-3">
        @foreach ($columnas as $columna)
            <section class="flex flex-col rounded-xl bg-slate-200/60 p-3" aria-labelledby="columna-{{ $columna['estado']->id_estado }}">
                <header class="mb-3 flex items-center justify-between px-1">
                    <h2 id="columna-{{ $columna['estado']->id_estado }}"><x-estado-pedido :estado="$columna['estado']" /></h2>
                    <span class="rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-slate-600 tabular-nums">{{ $columna['pedidos']->count() }}</span>
                </header>

                <div class="flex flex-1 flex-col gap-3">
                    @forelse ($columna['pedidos'] as $pedido)
                        <article class="tarjeta p-4">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <a href="{{ route('pedidos.show', $pedido) }}" class="text-sm font-semibold text-marca-600 hover:underline">#{{ $pedido->numero() }}</a>
                                    <p class="truncate font-medium text-slate-900">{{ $pedido->cliente->nombre }}</p>
                                </div>
                                <x-moneda :valor="$pedido->total" class="text-sm font-semibold text-slate-800" />
                            </div>
                            <p class="mt-1 flex items-center gap-1 text-xs text-slate-500">
                                <x-icono nombre="reloj" clase="size-3.5" />
                                {{ $pedido->fecha->format('d/m/Y H:i') }} · {{ (int) $pedido->unidades }} producto(s)
                            </p>
                            @if ($columna['siguiente'])
                                <form method="POST" action="{{ route('pedidos.estado', $pedido) }}" class="mt-3" data-envio-unico>
                                    @csrf
                                    <input type="hidden" name="id_estado" value="{{ $columna['siguiente']->id_estado }}">
                                    <button type="submit" class="btn btn-secundario w-full py-1.5 text-xs">
                                        {{ $columna['siguiente']->accion() }} &rarr;
                                    </button>
                                </form>
                            @endif
                        </article>
                    @empty
                        <p class="rounded-lg border-2 border-dashed border-slate-300 px-3 py-8 text-center text-sm text-slate-500">Sin pedidos en esta etapa</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</x-layouts.app>
