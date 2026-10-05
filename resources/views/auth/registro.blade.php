@php $tipo = old('tipo', $invitacion ? 'cliente' : (request()->filled('codigo') ? 'cliente' : 'emprendedor')); @endphp
<x-layouts.invitado :titulo="$invitacion ? 'Vea sus pedidos de '.$negocioInvita : 'Crear cuenta'"
                    :pestana="$invitacion ? 'Crear cuenta' : null"
                    :subtitulo="$invitacion ? 'Cree su cuenta y sus pedidos aparecerán aquí.' : null">
    @include('auth.partials.boton-google', ['codigo' => $invitacion?->codigoFormateado() ?? request('codigo'), 'texto' => 'Crear cuenta con Google'])

    <form method="POST" action="{{ route('registro.store') }}" class="space-y-5" novalidate data-envio-unico>
        @csrf

        @if ($invitacion)
            {{-- Llegó con el enlace de invitación: no necesita elegir ni escribir el código. --}}
            <input type="hidden" name="tipo" value="cliente">
            <input type="hidden" name="codigo" value="{{ $invitacion->codigoFormateado() }}">
            @error('codigo')<p class="error-campo">{{ $message }}</p>@enderror
        @else
            {{-- Dos caminos distintos: se elige primero y solo se muestran los campos de ese camino --}}
            <fieldset>
                <legend class="etiqueta">¿Cómo va a usar NEXO?</legend>
                <div class="space-y-2">
                    @foreach ([
                        'emprendedor' => 'Tengo un negocio y quiero registrar mis pedidos',
                        'cliente' => 'Soy cliente y quiero ver mis pedidos',
                    ] as $valor => $texto)
                        <label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-lg border border-borde-control bg-superficie px-4 py-3 text-sm transition-colors hover:bg-superficie-2 has-checked:border-marca has-checked:bg-seleccion has-focus-visible:outline-2 has-focus-visible:outline-marca">
                            <input type="radio" name="tipo" value="{{ $valor }}" @checked($tipo === $valor) class="size-4 shrink-0 accent-marca" data-tipo-cuenta>
                            <span class="font-medium">{{ $texto }}</span>
                        </label>
                    @endforeach
                </div>
                @error('tipo')<p class="error-campo">{{ $message }}</p>@enderror
            </fieldset>
        @endif

        <x-campo nombre="nombre" etiqueta="Su nombre" autocomplete="name" maxlength="100" requerido />

        @unless ($invitacion)
            <div data-solo-tipo="emprendedor">
                <x-campo nombre="negocio" etiqueta="Nombre de su negocio" autocomplete="organization" maxlength="100" requerido
                         ayuda="Así lo verán sus clientes, p. ej. «Dulces María»." />
            </div>
        @endunless

        <x-campo nombre="correo" etiqueta="Correo electrónico" tipo="email" autocomplete="email" placeholder="nombre@correo.com" maxlength="150" requerido />
        <x-campo nombre="contrasena" etiqueta="Contraseña" tipo="password" autocomplete="new-password" requerido
                 ayuda="Al menos 8 caracteres, con letras y números." />
        <x-campo nombre="contrasena_confirmation" etiqueta="Repita la contraseña" tipo="password" autocomplete="new-password" requerido />

        @unless ($invitacion)
            <div data-solo-tipo="cliente">
                <x-campo nombre="codigo" etiqueta="Código de cliente (opcional)" autocomplete="off" placeholder="XXXX-XXXX" maxlength="9"
                         class="font-mono tracking-widest uppercase" data-codigo :valor="request('codigo')"
                         ayuda="Se lo envía el negocio donde hizo su pedido. Si no lo tiene, puede agregarlo después." />
            </div>
        @endunless

        <button type="submit" class="btn btn-primario w-full" data-texto-envio="Creando cuenta…">Crear mi cuenta</button>
    </form>

    <div class="mt-8 border-t border-borde pt-6 text-sm text-texto-2">
        @if ($invitacion)
            <p>
                ¿Ya tiene cuenta? <a href="{{ route('login') }}" class="enlace">Inicie sesión</a>
                y escriba este código en «Mis pedidos»:
            </p>
            <p class="mt-2 font-mono text-base font-semibold tracking-widest text-texto select-all">{{ $invitacion->codigoFormateado() }}</p>
        @else
            <p>¿Ya tiene cuenta? <a href="{{ route('login') }}" class="enlace">Iniciar sesión</a></p>
        @endif
    </div>
</x-layouts.invitado>
