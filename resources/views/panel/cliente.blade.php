@php
    use App\Models\EstadoPedido;

    [$anteriores, $enCursoLista] = $pedidos->partition(fn ($p) => in_array($p->id_estado, EstadoPedido::FINALES, true));
@endphp
<x-layouts.app titulo="Mis pedidos" :encabezado="false">
    <x-bienvenida :titulo="'¡Hola, '.strtok(auth()->user()->nombre, ' ').'!'" antetitulo="Mis pedidos">
        @if ($pedidos->isEmpty())
            Conecte su cuenta con el código del negocio y aquí verá cómo van sus pedidos.
        @elseif ($enCurso)
            Tiene <b class="text-white">{{ $enCurso }} {{ $enCurso === 1 ? 'pedido en curso' : 'pedidos en curso' }}</b>. Toque uno para ver en qué etapa va.
        @else
            No tiene pedidos en curso. Aquí abajo están los anteriores.
        @endif
    </x-bienvenida>

    @if ($pedidos->isEmpty())
        {{-- Primera vez: una sola tarea, conectar la cuenta con el código del negocio. --}}
        <section class="panel p-5 sm:p-8" aria-labelledby="titulo-conectar">
            <x-ilustracion nombre="caja" class="mb-3" />
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
                                    <a href="{{ route('mis-pedidos.show', $pedido) }}" class="flex items-start gap-3 px-5 py-4 transition-colors hover:bg-superficie-2/60">
                                        <x-logo-negocio :negocio="$pedido->emprendedor" />
                                        <span class="min-w-0 flex-1">
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
                                        <x-entrega :pedido="$pedido" class="mt-1.5" />
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach

            {{-- Vinculación por código (RF-23): siempre visible para agregar otro negocio --}}
            <section class="panel p-5" aria-labelledby="titulo-vincular">
                <h2 id="titulo-vincular" class="titulo-seccion">Vincular otro negocio</h2>
                <p class="mt-1 mb-3 text-sm text-texto-2">
                    ¿Compró en otro negocio que usa NEXO? Escriba el código de invitación que le envió.
                    Cada código es de un solo uso.
                </p>
                <x-formulario-codigo class="max-w-md" />
            </section>
        </div>
    @endif
</x-layouts.app>
