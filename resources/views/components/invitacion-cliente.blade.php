@props(['cliente'])
@php
    $negocio = auth()->user()->nombreNegocio();
    $whatsapp = $cliente->enlaceWhatsApp($negocio);
    $mensaje = $cliente->mensajeInvitacion($negocio);
    $primerNombre = strtok($cliente->nombre, ' ');
@endphp
{{-- Invitación para que el cliente vea sus pedidos en su celular: vinculación por código de un solo uso (RF-23, RN-06). --}}
<section {{ $attributes->class('panel p-5') }} aria-labelledby="titulo-invitacion" id="invitacion">
    <h2 id="titulo-invitacion" class="titulo-seccion">Invitar a ver sus pedidos</h2>

    @if ($cliente->usuario)
        <p class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
            <x-icono nombre="ok" clase="size-3.5" /> Ve sus pedidos en su celular
        </p>
        <p class="mt-2 text-sm">{{ $primerNombre }} aceptó la invitación y ya ve en qué etapa va cada pedido.</p>
        <p class="meta truncate">Cuenta: {{ $cliente->usuario->correo }}</p>
        <p class="meta mt-1">El código de invitación ya se usó y dejó de funcionar.</p>
        <form method="POST" action="{{ route('clientes.codigo', $cliente) }}" class="mt-3"
              data-confirmar-titulo="¿Desconectar la cuenta {{ $cliente->usuario->correo }}?" data-confirmar="{{ $primerNombre }} dejará de ver sus pedidos hasta que use un código nuevo."
              data-confirmar-accion="Desconectar cuenta" data-confirmar-peligro>
            @csrf
            <button type="submit" class="btn btn-terciario btn-chico -ml-2.5 text-texto-2 hover:text-red-700">Desconectar cuenta</button>
        </form>
    @else
        <p class="mt-1 text-sm text-texto-2">{{ $primerNombre }} podrá ver en su celular cómo va cada pedido, sin tener que escribirle.</p>

        <ol class="mt-4 space-y-2 text-sm">
            @foreach (['Envíele la invitación por WhatsApp.', $primerNombre.' abre el enlace y crea su cuenta.', 'Sus pedidos aparecen en su celular con la etapa en la que van.'] as $paso)
                <li class="flex items-start gap-2.5">
                    <span class="grid size-6 shrink-0 place-items-center rounded-full bg-marca text-xs font-extrabold text-tinta-oro">{{ $loop->iteration }}</span>
                    <span class="pt-0.5">{{ $paso }}</span>
                </li>
            @endforeach
        </ol>

        @if ($whatsapp)
            <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn btn-whatsapp mt-4 w-full">
                <x-icono nombre="whatsapp" clase="size-5" /> Enviar invitación por WhatsApp
            </a>
        @else
            <button type="button" class="btn btn-primario mt-4 w-full" data-copiar="{{ $mensaje }}">Copiar invitación</button>
            <p class="ayuda mt-2">
                Péguela en el chat de {{ $primerNombre }}. Para enviarla directo por WhatsApp,
                <a href="{{ route('clientes.edit', $cliente) }}" class="enlace">agregue su teléfono</a>.
            </p>
        @endif

        <div class="mt-4 flex items-center justify-between gap-3 border-t border-borde pt-4">
            <div>
                <p class="meta">Código de invitación (sirve una sola vez)</p>
                <p class="font-mono text-lg font-semibold tracking-[0.15em] select-all">{{ $cliente->codigoFormateado() }}</p>
            </div>
            <button type="button" class="btn btn-secundario btn-chico" data-copiar="{{ $cliente->codigoFormateado() }}">Copiar</button>
        </div>

        <form method="POST" action="{{ route('clientes.codigo', $cliente) }}" class="mt-2"
              data-confirmar-titulo="¿Crear un código nuevo?" data-confirmar="El código actual dejará de funcionar. Tendrá que enviarle el nuevo a {{ $primerNombre }}."
              data-confirmar-accion="Crear código nuevo">
            @csrf
            <button type="submit" class="btn btn-terciario btn-chico -ml-2.5 text-texto-2">Crear un código nuevo</button>
        </form>
    @endif
</section>
