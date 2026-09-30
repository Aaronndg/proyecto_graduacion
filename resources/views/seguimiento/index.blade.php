<x-layouts.app titulo="Seguimiento" subtitulo="Sus pedidos activos por etapa. Avance cada uno con un clic.">
    <x-slot:acciones>
        <a href="{{ route('pedidos.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Nuevo pedido</a>
    </x-slot:acciones>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        @foreach ($columnas as $columna)
            <section class="flex flex-col rounded-2xl bg-stone-100/80 p-3" aria-labelledby="columna-{{ $columna['estado']->id_estado }}">
                <header class="mb-3 flex items-center justify-between px-2 pt-1">
                    <h2 id="columna-{{ $columna['estado']->id_estado }}" class="flex items-center gap-2 font-semibold text-stone-800">
                        <x-estado-pedido :estado="$columna['estado']" />
                    </h2>
                    <span class="flex size-7 items-center justify-center rounded-full bg-white text-xs font-bold text-stone-600 shadow-xs tabular-nums">{{ $columna['pedidos']->count() }}</span>
                </header>

                <div class="flex flex-1 flex-col gap-3">
                    @forelse ($columna['pedidos'] as $pedido)
                        <article class="tarjeta p-4">
                            <a href="{{ route('pedidos.show', $pedido) }}" class="group block">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="truncate font-semibold text-stone-900 group-hover:text-marca-700">{{ $pedido->cliente->nombre }}</p>
                                    <x-moneda :valor="$pedido->total" class="text-sm font-semibold text-stone-800" />
                                </div>
                                <p class="mt-1 text-xs text-stone-500">
                                    #{{ $pedido->numero() }} · {{ $pedido->fecha->format('d/m H:i') }} · {{ (int) $pedido->unidades }} producto(s)
                                </p>
                            </a>
                            @if ($columna['siguiente'])
                                <form method="POST" action="{{ route('pedidos.estado', $pedido) }}" class="mt-3" data-envio-unico>
                                    @csrf
                                    <button type="submit" name="id_estado" value="{{ $columna['siguiente']->id_estado }}" class="btn btn-secundario w-full py-2 text-xs">
                                        {{ $columna['siguiente']->accion() }} &rarr;
                                    </button>
                                </form>
                            @endif
                        </article>
                    @empty
                        <p class="flex flex-1 items-center justify-center rounded-xl border-2 border-dashed border-stone-200 px-3 py-10 text-center text-sm text-stone-400">Nada por aquí</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</x-layouts.app>
