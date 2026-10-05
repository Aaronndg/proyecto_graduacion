@php $tipo = old('tipo', $codigo ? 'cliente' : 'emprendedor'); @endphp
<x-layouts.invitado titulo="Ya casi está" pestana="Completar cuenta" subtitulo="Solo falta un dato para crear su cuenta.">
    <p class="mb-5 flex items-center gap-2 rounded-xl bg-superficie-2 px-4 py-3 text-sm">
        <x-icono nombre="usuario" clase="size-5 text-texto-2" /> Entró con Google como <b class="truncate">{{ $google['correo'] }}</b>
    </p>

    <form method="POST" action="{{ route('google.guardar') }}" class="space-y-5" novalidate data-envio-unico>
        @csrf
        <fieldset>
            <legend class="etiqueta">¿Cómo va a usar NEXO?</legend>
            <div class="space-y-2">
                @foreach ([
                    'emprendedor' => 'Tengo un negocio y quiero registrar mis pedidos',
                    'cliente' => 'Soy cliente y quiero ver mis pedidos',
                ] as $valor => $texto)
                    <label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-lg border border-borde-control bg-superficie px-4 py-3 text-sm transition-colors hover:bg-superficie-2 has-checked:border-marca has-checked:bg-seleccion">
                        <input type="radio" name="tipo" value="{{ $valor }}" @checked($tipo === $valor) class="size-4 shrink-0 accent-marca" data-tipo-cuenta>
                        <span class="font-medium">{{ $texto }}</span>
                    </label>
                @endforeach
            </div>
            @error('tipo')<p class="error-campo">{{ $message }}</p>@enderror
        </fieldset>

        <x-campo nombre="nombre" etiqueta="Su nombre" :valor="$google['nombre']" autocomplete="name" maxlength="100" requerido />

        <div data-solo-tipo="emprendedor">
            <x-campo nombre="negocio" etiqueta="Nombre de su negocio" autocomplete="organization" maxlength="100" requerido
                     ayuda="Así lo verán sus clientes, p. ej. «Dulces María»." />
        </div>

        <div data-solo-tipo="cliente">
            <x-campo nombre="codigo" etiqueta="Código de cliente (opcional)" autocomplete="off" placeholder="XXXX-XXXX" maxlength="9"
                     class="font-mono tracking-widest uppercase" data-codigo :valor="$codigo ? substr($codigo, 0, 4).'-'.substr($codigo, 4) : null"
                     ayuda="Se lo envía el negocio donde hizo su pedido. Si no lo tiene, puede agregarlo después." />
        </div>

        <button type="submit" class="btn btn-primario w-full" data-texto-envio="Creando cuenta…">Crear mi cuenta</button>
    </form>
</x-layouts.invitado>
