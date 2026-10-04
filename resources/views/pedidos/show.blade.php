@php
    use App\Models\EstadoPedido;

    $cancelar = $estadosSiguientes->firstWhere('id_estado', EstadoPedido::CANCELADO);
    $avances = $estadosSiguientes->where('id_estado', '!=', EstadoPedido::CANCELADO)->values();
    $siguiente = $avances->first();
    $saltos = $avances->slice(1);
    $cliente = $pedido->cliente;
    $cierre = $pedido->estado->esFinal() ? $pedido->historial->last() : null;
    $hayErrorEstado = $errors->has('observacion') || $errors->has('id_estado');
@endphp
<x-layouts.app :titulo="'#'.$pedido->numero()"
                :subtitulo="$cliente->nombre.' · '.$pedido->fecha->translatedFormat('j \d\e F \d\e Y, H:i')"
                :ruta="['Pedidos' => route('pedidos.index')]">
    @if ($editable)
        <x-slot:acciones>
            <x-menu-acciones etiqueta="Más acciones del pedido">
                <a href="{{ route('pedidos.edit', $pedido) }}" class="menu-opcion"><x-icono nombre="editar" clase="size-4 text-texto-2" /> Editar pedido</a>
                @if ($cancelar)
                    <button type="button" class="menu-opcion menu-opcion-peligro" data-abrir-dialogo="dialogo-cancelar">
                        <x-icono nombre="cerrar" clase="size-4" /> Cancelar pedido
                    </button>
                @endif
            </x-menu-acciones>
        </x-slot:acciones>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_23rem] lg:items-start">

        {{-- ===== Estado: lo primero en el teléfono, a la derecha en escritorio ===== --}}
        <div class="space-y-6 lg:col-start-2 lg:row-start-1">
            <section class="panel p-5" aria-labelledby="titulo-estado">
                <h2 id="titulo-estado" class="sr-only">Estado del pedido</h2>
                <div class="mb-5 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <x-estado-pedido :estado="$pedido->estado" />
                    <span class="text-sm text-texto-2">{{ $pedido->estado->descripcion }}</span>
                </div>

                <x-progreso-pedido :pedido="$pedido" />

                @if ($estadosSiguientes->isNotEmpty())
                    <form method="POST" action="{{ route('pedidos.estado', $pedido) }}" class="mt-6 space-y-3" novalidate data-envio-unico>
                        @csrf
                        <h3 class="sr-only">Actualizar estado</h3>

                        @if ($siguiente)
                            <button type="submit" name="id_estado" value="{{ $siguiente->id_estado }}" data-texto-envio="Actualizando…" class="btn btn-primario w-full">
                                {{ $siguiente->accion() }}
                            </button>
                        @endif

                        @foreach ($saltos as $estado)
                            <button type="submit" name="id_estado" value="{{ $estado->id_estado }}" data-texto-envio="Actualizando…" class="btn btn-secundario w-full">
                                Pasar directo a «{{ $estado->nombre }}»
                            </button>
                        @endforeach

                        {{-- Nota opcional para el historial (abierta si hubo un error) --}}
                        <details class="group" @if ($hayErrorEstado && ! old('cancelacion')) open @endif>
                            <summary class="inline-flex min-h-9 cursor-pointer list-none items-center gap-1.5 text-sm text-texto-2 hover:text-texto [&::-webkit-details-marker]:hidden">
                                <x-icono nombre="flecha-derecha" clase="size-4 transition-transform group-open:rotate-90" />
                                Agregar una nota al historial
                            </summary>
                            <label for="observacion" class="sr-only">Nota para el historial</label>
                            <textarea id="observacion" name="observacion" rows="2" maxlength="255" placeholder="Ej.: Sale a entrega a las 3 p. m."
                                      @class(['campo mt-2', 'campo-error' => $errors->has('observacion') && ! old('cancelacion')])>{{ old('cancelacion') ? '' : old('observacion') }}</textarea>
                        </details>

                        @if (! old('cancelacion'))
                            @error('observacion')<p class="error-campo">{{ $message }}</p>@enderror
                            @error('id_estado')<p class="error-campo">{{ $message }}</p>@enderror
                        @endif
                    </form>
                @elseif ($cierre)
                    <p class="mt-6 rounded-lg bg-superficie-2 px-4 py-3 text-sm">
                        {{ $pedido->estado->nombre }} el {{ $cierre->fecha_hora->format('d/m/Y') }} a las {{ $cierre->fecha_hora->format('H:i') }}.
                        @if ($pedido->id_estado === EstadoPedido::CANCELADO && $cierre->observacion)
                            <span class="mt-1 block text-texto-2">Motivo: {{ $cierre->observacion }}</span>
                        @endif
                    </p>
                @endif
            </section>

            {{-- Historial (RF-11): compacto, el más reciente arriba --}}
            <section class="hidden lg:block" aria-labelledby="titulo-historial-esc">
                @include('pedidos.partials.historial', ['idTitulo' => 'titulo-historial-esc'])
            </section>
        </div>

        {{-- ===== Lo que se pidió y el cliente ===== --}}
        <div class="space-y-6 lg:col-start-1 lg:row-start-1">
            <section class="panel overflow-hidden" aria-labelledby="titulo-productos">
                <h2 id="titulo-productos" class="titulo-seccion px-5 pt-4 pb-2">Productos</h2>
                <ul>
                    @foreach ($pedido->detalles as $detalle)
                        <li class="flex items-baseline justify-between gap-4 border-b border-borde px-5 py-3 last:border-b-0">
                            <span class="min-w-0">
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

            <section class="panel p-5" aria-labelledby="titulo-cliente">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 id="titulo-cliente" class="font-medium">{{ $cliente->nombre }}</h2>
                        <p class="text-sm text-texto-2">{{ collect([$cliente->telefono, $cliente->correo])->filter()->implode(' · ') ?: 'Sin datos de contacto' }}</p>
                        @if ($cliente->direccion)<p class="text-sm text-texto-2">{{ $cliente->direccion }}</p>@endif
                    </div>
                    <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-secundario btn-chico">Ver cliente</a>
                </div>

                {{-- Invitación compacta: la versión completa (código, código nuevo) está en la ficha del cliente --}}
                @unless ($cliente->id_usuario)
                    @php $whatsapp = $cliente->enlaceWhatsApp(auth()->user()->negocio ?? auth()->user()->nombre); @endphp
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-borde pt-4 text-sm">
                        <span class="text-texto-2">{{ strtok($cliente->nombre, ' ') }} aún no ve sus pedidos en línea.</span>
                        @if ($whatsapp)
                            <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn btn-terciario btn-chico">Enviar invitación por WhatsApp</a>
                        @else
                            <a href="{{ route('clientes.show', $cliente) }}" class="enlace">Ver cómo invitarle</a>
                        @endif
                    </div>
                @endunless
            </section>

            <section class="lg:hidden" aria-labelledby="titulo-historial-movil">
                @include('pedidos.partials.historial', ['idTitulo' => 'titulo-historial-movil'])
            </section>
        </div>
    </div>

    {{-- Cancelar: pide el motivo (obligatorio) en un diálogo propio; misma acción y reglas que antes --}}
    @if ($cancelar)
        <dialog id="dialogo-cancelar" class="dialogo m-auto w-[calc(100%-2rem)] max-w-md rounded-xl border border-borde bg-superficie p-0 text-texto shadow-flotante"
                aria-labelledby="cancelar-titulo" @if ($hayErrorEstado && old('cancelacion')) data-abrir @endif>
            <form method="POST" action="{{ route('pedidos.estado', $pedido) }}" class="p-6" data-envio-unico>
                @csrf
                <input type="hidden" name="cancelacion" value="1">
                <h2 id="cancelar-titulo" class="titulo-seccion">¿Cancelar el pedido #{{ $pedido->numero() }}?</h2>
                <p class="mt-1 text-sm text-texto-2">El pedido quedará cancelado y no podrá avanzar. Esta acción no se puede deshacer.</p>

                <label for="motivo" class="etiqueta mt-5">Motivo de la cancelación <span class="text-red-600" aria-hidden="true">*</span></label>
                <textarea id="motivo" name="observacion" rows="3" maxlength="255" required placeholder="Ej.: El cliente ya no lo necesita"
                          @class(['campo', 'campo-error' => $errors->has('observacion') && old('cancelacion')])
                          @if ($errors->has('observacion') && old('cancelacion')) aria-invalid="true" aria-describedby="motivo-error" @endif>{{ old('cancelacion') ? old('observacion') : '' }}</textarea>
                @if (old('cancelacion'))
                    @error('observacion')<p id="motivo-error" class="error-campo">{{ $message }}</p>@enderror
                @endif

                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" class="btn btn-secundario" data-cerrar-dialogo>Volver</button>
                    <button type="submit" name="id_estado" value="{{ $cancelar->id_estado }}" data-texto-envio="Cancelando…" class="btn btn-peligro">Cancelar pedido</button>
                </div>
            </form>
        </dialog>
    @endif
</x-layouts.app>
