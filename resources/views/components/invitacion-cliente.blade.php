@props(['cliente'])
@php
    $negocio = auth()->user()->negocio ?? auth()->user()->nombre;
    $whatsapp = $cliente->enlaceWhatsApp($negocio);
    $primerNombre = strtok($cliente->nombre, ' ');
@endphp
{{-- Invitación para que el cliente siga sus pedidos desde su celular (vinculación por código, RN-06). --}}
<section {{ $attributes->class('tarjeta p-5') }}>
    @if ($cliente->usuario)
        <div class="flex items-start gap-3">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                <x-icono nombre="ok" clase="size-5" />
            </span>
            <div class="min-w-0">
                <h3 class="font-semibold text-stone-900">{{ $primerNombre }} ya sigue sus pedidos en línea</h3>
                <p class="mt-0.5 truncate text-sm text-stone-500">Cuenta: {{ $cliente->usuario->correo }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('clientes.codigo', $cliente) }}" class="mt-4"
              data-confirmar="¿Desconectar la cuenta {{ $cliente->usuario->correo }}? {{ $primerNombre }} dejará de ver sus pedidos hasta que use un código nuevo.">
            @csrf
            <button type="submit" class="text-xs font-medium text-stone-500 hover:text-red-600 hover:underline">Desconectar cuenta</button>
        </form>
    @else
        <h3 class="font-semibold text-stone-900">Que {{ $primerNombre }} vea sus pedidos desde el celular</h3>
        <p class="mt-1 text-sm text-stone-500">Envíele su invitación. Al abrirla crea su cuenta y ya puede ver en qué etapa va su pedido.</p>

        @if ($whatsapp)
            <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn btn-whatsapp mt-4 w-full py-3">
                <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2a9.9 9.9 0 0 0-8.5 14.98L2 22l5.16-1.5A9.9 9.9 0 1 0 12.04 2Zm0 18.13a8.2 8.2 0 0 1-4.2-1.15l-.3-.18-3.07.9.92-3-.2-.31a8.23 8.23 0 1 1 6.85 3.74Zm4.52-6.16c-.25-.12-1.46-.72-1.69-.8-.23-.08-.39-.12-.56.12-.16.25-.64.8-.78.97-.14.16-.29.18-.54.06a6.73 6.73 0 0 1-3.36-2.94c-.25-.44.25-.41.72-1.36.08-.16.04-.3-.02-.43-.06-.12-.56-1.34-.76-1.83-.2-.48-.4-.41-.56-.42h-.47a.9.9 0 0 0-.66.31 2.76 2.76 0 0 0-.86 2.05 4.8 4.8 0 0 0 1 2.55 11 11 0 0 0 4.22 3.73c1.57.68 2.19.74 2.97.62.48-.07 1.46-.6 1.67-1.18.2-.58.2-1.08.14-1.18-.06-.1-.23-.16-.47-.28Z"/></svg>
                Enviar invitación por WhatsApp
            </a>
        @else
            <p class="mt-4 rounded-xl bg-amber-50 px-3 py-2 text-sm text-amber-800">
                Agregue el teléfono de {{ $primerNombre }} para enviarle la invitación por WhatsApp.
            </p>
        @endif

        <div class="mt-4 flex items-center justify-between gap-3 rounded-xl bg-stone-50 px-4 py-3">
            <div>
                <p class="text-xs text-stone-500">O compártale su código</p>
                <p class="font-mono text-lg font-bold tracking-[0.15em] text-stone-900 select-all">{{ $cliente->codigoFormateado() }}</p>
            </div>
            <button type="button" class="btn btn-secundario px-3 py-1.5 text-xs" data-copiar="{{ $cliente->codigoFormateado() }}">Copiar</button>
        </div>

        <form method="POST" action="{{ route('clientes.codigo', $cliente) }}" class="mt-3 text-center"
              data-confirmar="¿Crear un código nuevo? El código actual dejará de funcionar.">
            @csrf
            <button type="submit" class="text-xs text-stone-500 hover:text-stone-800 hover:underline">Crear un código nuevo</button>
        </form>
    @endif
</section>
