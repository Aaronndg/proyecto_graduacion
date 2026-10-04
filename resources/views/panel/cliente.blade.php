@php
    use App\Models\EstadoPedido;

    [$anteriores, $enCursoLista] = $pedidos->partition(fn ($p) => in_array($p->id_estado, EstadoPedido::FINALES, true));
@endphp
<x-layouts.app titulo="Mis pedidos" :subtitulo="'Hola, '.strtok(auth()->user()->nombre, ' ').'.'">
    @if ($pedidos->isEmpty())
        {{-- Primera vez: una sola tarea, conectar la cuenta con el código del negocio. --}}
        <section class="panel p-5 sm:p-8" aria-labelledby="titulo-conectar">
            <h2 id="titulo-conectar" class="titulo-seccion">Conecte su cuenta con el negocio</h2>
            <p class="mt-1 text-texto-2">Escriba el código que le envió el negocio donde hizo su pedido.</p>

            <x-formulario-codigo grande class="mt-6" />

            <p class="ayuda mt-4">
                Es de 8 letras y números, como <span class="font-mono whitespace-nowrap">K7QM-4XPA</span>.
                Si no lo tiene, pídaselo al negocio.
            </p>
        </section>
    @else
        <div class="space-y-8">
            @foreach (['En curso' => $enCursoLista, 'Anteriores' => $anteriores] as $grupo => $lista)
                @continue($grupo === 'Anteriores' && $lista->isEmpty())
                <section aria-labelledby="grupo-{{ $loop->index }}">
                    <h2 id="grupo-{{ $loop->index }}" class="titulo-seccion mb-3">{{ $grupo }}</h2>

                    @if ($lista->isEmpty())
                        <p class="panel px-5 py-6 text-sm text-texto-2">No tiene pedidos en curso.</p>
                    @else
                        <ul class="panel overflow-hidden">
                            @foreach ($lista as $pedido)
                                <li class="border-b border-borde last:border-b-0">
                                    <a href="{{ route('mis-pedidos.show', $pedido) }}" class="block px-5 py-4 transition-colors hover:bg-superficie-2/60">
                                        <span class="flex items-start justify-between gap-3">
                                            <span @class(['font-medium', 'text-texto-2' => $grupo === 'Anteriores'])>{{ $pedido->estado->mensajeCliente() }}</span>
                                            <x-estado-pedido :estado="$pedido->estado" class="shrink-0" />
                                        </span>
                                        <span class="mt-1 flex items-baseline justify-between gap-3">
                                            <span class="meta min-w-0 truncate">
                                                {{ $pedido->emprendedor->negocio ?? $pedido->emprendedor->nombre }} · #{{ $pedido->numero() }} · {{ $pedido->fecha->translatedFormat('j M') }}
                                            </span>
                                            <x-moneda :valor="$pedido->total" class="shrink-0 text-sm font-medium" />
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach

            <details class="group" @if ($errors->has('codigo')) open @endif>
                <summary class="inline-flex min-h-9 cursor-pointer list-none items-center gap-1.5 text-sm text-texto-2 hover:text-texto [&::-webkit-details-marker]:hidden">
                    <x-icono nombre="flecha-derecha" clase="size-4 transition-transform group-open:rotate-90" />
                    ¿Compró en otro negocio? Agregue su código
                </summary>
                <div class="panel mt-2 p-5">
                    <p class="mb-3 text-sm text-texto-2">Escriba el código de cliente que le envió ese negocio.</p>
                    <x-formulario-codigo class="max-w-md" />
                </div>
            </details>
        </div>
    @endif
</x-layouts.app>
