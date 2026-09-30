<x-layouts.invitado titulo="Crear cuenta">
    <form method="POST" action="{{ route('registro.store') }}" class="space-y-5" novalidate>
        @csrf

        <fieldset>
            <legend class="etiqueta">Tipo de cuenta <span class="text-red-600" aria-hidden="true">*</span></legend>
            <div class="grid grid-cols-2 gap-3">
                @foreach (['emprendedor' => ['Emprendedor', 'Administro mi negocio'], 'cliente' => ['Cliente', 'Consulto mis pedidos']] as $valor => [$texto, $detalle])
                    <label class="flex cursor-pointer flex-col rounded-lg border border-slate-300 p-3 text-sm has-checked:border-marca-500 has-checked:bg-marca-50 has-checked:ring-2 has-checked:ring-marca-500/30">
                        <span class="flex items-center gap-2 font-semibold text-slate-800">
                            <input type="radio" name="tipo" value="{{ $valor }}" @checked(old('tipo', request()->filled('codigo') ? 'cliente' : 'emprendedor') === $valor) class="accent-marca-600" data-tipo-cuenta>
                            {{ $texto }}
                        </span>
                        <span class="mt-1 text-xs text-slate-500">{{ $detalle }}</span>
                    </label>
                @endforeach
            </div>
            @error('tipo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </fieldset>

        <x-campo nombre="nombre" etiqueta="Nombre completo" autocomplete="name" requerido />
        <div id="campo-negocio">
            <x-campo nombre="negocio" etiqueta="Nombre del negocio" autocomplete="organization" requerido />
        </div>
        <div id="campo-codigo">
            <x-campo nombre="codigo" etiqueta="Código de vinculación (opcional)" autocomplete="off" placeholder="Ej.: K7QM-4XPA" class="uppercase"
                     :valor="request('codigo')" ayuda="Lo recibe del negocio donde hizo su pedido. También puede ingresarlo después." />
        </div>
        <x-campo nombre="correo" etiqueta="Correo electrónico" tipo="email" autocomplete="email" requerido />
        <x-campo nombre="contrasena" etiqueta="Contraseña" tipo="password" autocomplete="new-password" requerido
                 ayuda="Mínimo 8 caracteres, con letras y números." />
        <x-campo nombre="contrasena_confirmation" etiqueta="Confirmar contraseña" tipo="password" autocomplete="new-password" requerido />

        <button type="submit" class="btn btn-primario w-full py-2.5">Crear cuenta</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        ¿Ya tiene cuenta?
        <a href="{{ route('login') }}" class="font-semibold text-marca-600 hover:underline">Iniciar sesión</a>
    </p>

    <script>
        // El nombre del negocio solo aplica a emprendedores.
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
</x-layouts.invitado>
