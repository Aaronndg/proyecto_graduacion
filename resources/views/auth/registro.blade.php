@php $tipo = old('tipo', $invitacion ? 'cliente' : (request()->filled('codigo') ? 'cliente' : 'emprendedor')); @endphp
<x-layouts.invitado :titulo="$invitacion ? 'Vea sus pedidos en línea' : 'Crear cuenta'"
                    :subtitulo="$invitacion ? null : 'Elija cómo va a usar el sistema.'">
    <form method="POST" action="{{ route('registro.store') }}" class="space-y-5" novalidate data-envio-unico>
        @csrf

        @if ($invitacion)
            {{-- El cliente llegó con el enlace de invitación: no necesita elegir ni escribir el código. --}}
            <div class="flex items-center gap-4 rounded-2xl border border-marca-200 bg-marca-50 p-4">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white text-marca-600 shadow-xs">
                    <x-icono nombre="negocio" clase="size-6" />
                </span>
                <p class="text-sm text-marca-900">
                    <strong class="block text-base">{{ $negocioInvita }}</strong>
                    le invita a seguir sus pedidos. Solo cree su cuenta y listo.
                </p>
            </div>
            <input type="hidden" name="tipo" value="cliente">
            <input type="hidden" name="codigo" value="{{ $invitacion->codigoFormateado() }}">
            @error('codigo')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        @else
            <fieldset>
                <legend class="sr-only">Tipo de cuenta</legend>
                <div class="grid grid-cols-2 gap-3">
                    @foreach ([
                        'cliente' => ['Quiero ver mis pedidos', 'Soy cliente', 'pedido'],
                        'emprendedor' => ['Quiero administrar mi negocio', 'Soy emprendedor', 'negocio'],
                    ] as $valor => [$texto, $detalle, $icono])
                        <label class="flex cursor-pointer flex-col gap-2 rounded-2xl border border-stone-200 p-4 text-sm transition hover:border-stone-300 has-checked:border-marca-500 has-checked:bg-marca-50/60 has-checked:ring-4 has-checked:ring-marca-500/10">
                            <input type="radio" name="tipo" value="{{ $valor }}" @checked($tipo === $valor) class="sr-only" data-tipo-cuenta>
                            <x-icono :nombre="$icono" clase="size-6 text-marca-600" />
                            <span class="font-semibold leading-snug text-stone-900">{{ $texto }}</span>
                            <span class="text-xs text-stone-500">{{ $detalle }}</span>
                        </label>
                    @endforeach
                </div>
                @error('tipo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </fieldset>
        @endif

        <x-campo nombre="nombre" etiqueta="Su nombre" autocomplete="name" maxlength="100" requerido />

        @unless ($invitacion)
            <div id="campo-negocio">
                <x-campo nombre="negocio" etiqueta="Nombre de su negocio" autocomplete="organization" maxlength="100" requerido />
            </div>
        @endunless

        <x-campo nombre="correo" etiqueta="Correo electrónico" tipo="email" autocomplete="email" placeholder="nombre@correo.com" maxlength="150" requerido />
        <x-campo nombre="contrasena" etiqueta="Contraseña" tipo="password" autocomplete="new-password" requerido
                 ayuda="Al menos 8 caracteres, con letras y números." />
        <x-campo nombre="contrasena_confirmation" etiqueta="Repita la contraseña" tipo="password" autocomplete="new-password" requerido />

        @unless ($invitacion)
            <div id="campo-codigo" class="rounded-2xl border border-dashed border-stone-300 p-4">
                <x-campo nombre="codigo" etiqueta="Código de cliente (opcional)" autocomplete="off" placeholder="XXXX-XXXX" maxlength="9"
                         class="font-mono tracking-widest uppercase" data-codigo :valor="request('codigo')"
                         ayuda="Se lo envía el negocio donde hizo su pedido. Si no lo tiene, puede agregarlo después." />
            </div>
        @endunless

        <button type="submit" class="btn btn-primario w-full py-3 text-base">Crear mi cuenta</button>
    </form>

    @if ($invitacion)
        <p class="mt-8 rounded-2xl bg-stone-50 p-4 text-center text-sm text-stone-600">
            ¿Ya tiene cuenta? <a href="{{ route('login') }}" class="enlace">Inicie sesión</a>
            y escriba este código en «Mis pedidos»:
            <span class="mt-1 block font-mono text-base font-bold tracking-widest text-stone-900 select-all">{{ $invitacion->codigoFormateado() }}</span>
        </p>
    @else
        <p class="mt-8 text-center text-sm text-stone-600">
            ¿Ya tiene cuenta?
            <a href="{{ route('login') }}" class="enlace">Iniciar sesión</a>
        </p>
    @endif

    @unless ($invitacion)
        <script>
            // Muestra solo los campos que corresponden al tipo de cuenta elegido.
            (() => {
                const actualizar = () => {
                    const tipo = document.querySelector('[data-tipo-cuenta]:checked')?.value;
                    document.getElementById('campo-negocio').hidden = tipo !== 'emprendedor';
                    document.getElementById('campo-codigo').hidden = tipo !== 'cliente';
                };
                document.querySelectorAll('[data-tipo-cuenta]').forEach((r) => r.addEventListener('change', actualizar));
                actualizar();
            })();
        </script>
    @endunless
</x-layouts.invitado>
