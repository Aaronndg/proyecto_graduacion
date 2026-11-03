@props(['cliente', 'contexto' => 'pedido'])
@php
    $negocio = auth()->user()->nombreNegocio();
    $whatsapp = $cliente->enlaceWhatsApp($negocio);
    $primerNombre = strtok($cliente->nombre, ' ');
@endphp
{{-- Aviso destacado justo después de registrar el pedido o el cliente: el momento en que tiene sentido invitarle (RF-23). --}}
<section {{ $attributes->class('panel-destacado mb-6 flex flex-col gap-4 p-5 sm:flex-row sm:items-center') }} data-aviso aria-labelledby="titulo-aviso-invitacion">
    <x-ilustracion nombre="caja" class="hidden shrink-0 sm:block" />
    <div class="min-w-0 flex-1">
        <h2 id="titulo-aviso-invitacion" class="titulo-seccion">
            {{ $contexto === 'pedido' ? "¿Quiere que {$primerNombre} siga este pedido desde su celular?" : "¿Quiere que {$primerNombre} vea sus pedidos desde su celular?" }}
        </h2>
        <p class="mt-1 text-sm text-texto-2">
            Envíele la invitación: abre el enlace, crea su cuenta y ve en qué etapa va {{ $contexto === 'pedido' ? 'su pedido' : 'cada pedido' }}, sin tener que escribirle.
        </p>
    </div>
    <div class="flex shrink-0 flex-wrap gap-2">
        @if ($whatsapp)
            <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn btn-whatsapp"><x-icono nombre="whatsapp" clase="size-5" /> Enviar invitación</a>
        @else
            <a href="{{ route('clientes.show', $cliente) }}#invitacion" class="btn btn-primario">Ver cómo invitarle</a>
        @endif
        <button type="button" class="btn btn-terciario" data-cerrar-aviso>Ahora no</button>
    </div>
</section>
