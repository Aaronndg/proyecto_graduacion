@php
    $editando = $usuario->exists;
    $esPropio = $editando && $usuario->is(auth()->user());
@endphp
<x-layouts.app :titulo="$editando ? 'Editar usuario' : 'Nuevo usuario'">

    <x-slot:acciones>
        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secundario">&larr; Usuarios</a>
    </x-slot:acciones>
    <div class="max-w-2xl">

        <form method="POST" action="{{ $editando ? route('admin.usuarios.update', $usuario) : route('admin.usuarios.store') }}" class="tarjeta space-y-5 p-6" novalidate>
            @csrf
            @if ($editando) @method('PUT') @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <x-campo nombre="nombre" etiqueta="Nombre completo" :valor="$usuario->nombre" requerido />
                <x-campo nombre="correo" etiqueta="Correo electrónico" tipo="email" :valor="$usuario->correo" requerido />

                <div>
                    <label for="id_rol" class="etiqueta">Rol <span class="text-red-600" aria-hidden="true">*</span></label>
                    <select id="id_rol" name="id_rol" class="campo" @disabled($esPropio) data-rol>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id_rol }}" @selected((int) old('id_rol', $usuario->id_rol) === $rol->id_rol)>{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                    @if ($esPropio)
                        <input type="hidden" name="id_rol" value="{{ $usuario->id_rol }}">
                        <p class="mt-1 text-xs text-stone-500">No puede cambiar su propio rol.</p>
                    @endif
                    @error('id_rol')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div id="campo-negocio">
                    <x-campo nombre="negocio" etiqueta="Nombre del negocio" :valor="$usuario->negocio" requerido />
                </div>

                <x-campo nombre="contrasena" :etiqueta="$editando ? 'Nueva contraseña' : 'Contraseña'" tipo="password" autocomplete="new-password"
                         :requerido="! $editando" :ayuda="$editando ? 'Déjela vacía para conservar la actual.' : 'Mínimo 8 caracteres, con letras y números.'" />
                <x-campo nombre="contrasena_confirmation" etiqueta="Confirmar contraseña" tipo="password" autocomplete="new-password" :requerido="! $editando" />
            </div>

            <label class="flex items-center gap-3 text-sm text-stone-700">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1" class="size-4 accent-marca-600" @checked(old('activo', $usuario->activo)) @disabled($esPropio)>
                Cuenta activa (puede iniciar sesión)
                @if ($esPropio)<input type="hidden" name="activo" value="1">@endif
            </label>

            <div class="flex justify-end gap-3 border-t border-stone-200 pt-5">
                <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secundario">Cancelar</a>
                <button type="submit" class="btn btn-primario">{{ $editando ? 'Guardar cambios' : 'Registrar usuario' }}</button>
            </div>
        </form>
    </div>

    <script>
        // El nombre del negocio solo se solicita para emprendedores.
        (() => {
            const rol = document.querySelector('[data-rol]');
            const actualizar = () => { document.getElementById('campo-negocio').hidden = rol.value !== '{{ \App\Models\Rol::EMPRENDEDOR }}'; };
            rol.addEventListener('change', actualizar);
            actualizar();
        })();
    </script>
</x-layouts.app>
