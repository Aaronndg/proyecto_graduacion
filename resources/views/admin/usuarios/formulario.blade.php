@php
    use App\Models\Rol;

    $editando = $usuario->exists;
    $esPropio = $editando && $usuario->is(auth()->user());
    $activa = (bool) old('activo', $usuario->activo);
@endphp
<x-layouts.app :titulo="$editando ? 'Editar usuario' : 'Nuevo usuario'" :ruta="['Usuarios' => route('admin.usuarios.index')]"
                :subtitulo="$editando && ! $usuario->activo ? 'Cuenta desactivada: no puede iniciar sesión.' : null">
    @if ($editando && ! $esPropio)
        <x-slot:acciones>
            <x-menu-acciones etiqueta="Más acciones de la cuenta">
                <form method="POST" action="{{ route('admin.usuarios.estado', $usuario) }}"
                      data-confirmar-titulo="{{ $usuario->activo ? '¿Desactivar la cuenta de '.$usuario->nombre.'?' : '¿Activar la cuenta de '.$usuario->nombre.'?' }}"
                      data-confirmar="{{ $usuario->activo ? 'No podrá iniciar sesión hasta que la active de nuevo. Sus datos se conservan.' : 'Podrá volver a iniciar sesión.' }}"
                      data-confirmar-accion="{{ $usuario->activo ? 'Desactivar cuenta' : 'Activar cuenta' }}" @if ($usuario->activo) data-confirmar-peligro @endif>
                    @csrf
                    @method('PATCH')
                    <button type="submit" @class(['menu-opcion', 'menu-opcion-peligro' => $usuario->activo])>
                        <x-icono :nombre="$usuario->activo ? 'cerrar' : 'ok'" clase="size-4" />
                        {{ $usuario->activo ? 'Desactivar cuenta' : 'Activar cuenta' }}
                    </button>
                </form>
            </x-menu-acciones>
        </x-slot:acciones>
    @endif

    <form method="POST" action="{{ $editando ? route('admin.usuarios.update', $usuario) : route('admin.usuarios.store') }}"
          class="panel max-w-xl p-5 sm:p-6" novalidate data-envio-unico>
        @csrf
        @if ($editando) @method('PUT') @endif

        <fieldset class="space-y-5">
            <legend class="titulo-seccion mb-4">Cuenta</legend>
            <x-campo nombre="nombre" etiqueta="Nombre completo" :valor="$usuario->nombre" maxlength="100" requerido autocomplete="off" />
            <x-campo nombre="correo" etiqueta="Correo electrónico" tipo="email" :valor="$usuario->correo" maxlength="150" requerido autocomplete="off" />

            <div>
                <label for="id_rol" class="etiqueta">Rol <span class="text-red-600" aria-hidden="true">*</span></label>
                <select id="id_rol" name="id_rol" @class(['campo', 'campo-error' => $errors->has('id_rol')]) @disabled($esPropio) data-tipo-cuenta>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id_rol }}" @selected((int) old('id_rol', $usuario->id_rol) === $rol->id_rol)>{{ $rol->nombre }}</option>
                    @endforeach
                </select>
                @if ($esPropio)
                    <input type="hidden" name="id_rol" value="{{ $usuario->id_rol }}">
                    <p class="ayuda">No puede cambiar su propio rol.</p>
                @endif
                @error('id_rol')<p class="error-campo">{{ $message }}</p>@enderror
            </div>

            <div data-solo-tipo="{{ Rol::EMPRENDEDOR }}">
                <x-campo nombre="negocio" etiqueta="Nombre del negocio" :valor="$usuario->negocio" maxlength="100" requerido />
            </div>
        </fieldset>

        <fieldset class="mt-6 space-y-5 border-t border-borde pt-5">
            <legend class="sr-only">Contraseña</legend>
            <p class="titulo-seccion">Contraseña</p>
            <x-campo nombre="contrasena" :etiqueta="$editando ? 'Nueva contraseña' : 'Contraseña'" tipo="password" autocomplete="new-password"
                     :requerido="! $editando" :ayuda="$editando ? 'Déjela vacía para conservar la actual.' : 'Mínimo 8 caracteres, con letras y números.'" />
            <x-campo nombre="contrasena_confirmation" :etiqueta="$editando ? 'Repita la nueva contraseña' : 'Repita la contraseña'" tipo="password" autocomplete="new-password" :requerido="! $editando" />
        </fieldset>

        {{-- Interruptor (casilla con role="switch"); el hidden envía 0 cuando está apagado --}}
        <div class="mt-6 border-t border-borde pt-5">
            <input type="hidden" name="activo" value="0">
            @if ($esPropio)<input type="hidden" name="activo" value="1">@endif
            <label @class(['flex items-start justify-between gap-4', 'cursor-pointer' => ! $esPropio])>
                <span>
                    <span class="block text-sm font-medium">Cuenta activa</span>
                    <span class="ayuda block">{{ $esPropio ? 'No puede desactivar su propia cuenta.' : 'Si la apaga, no podrá iniciar sesión. Sus datos se conservan.' }}</span>
                </span>
                <input type="checkbox" name="activo" value="1" role="switch" @checked($activa) @disabled($esPropio)
                       class="relative mt-0.5 h-6 w-11 shrink-0 cursor-pointer appearance-none rounded-full bg-stone-300 transition-colors before:absolute before:top-0.5 before:left-0.5 before:size-5 before:rounded-full before:bg-white before:shadow-sm before:transition-transform checked:bg-marca checked:before:translate-x-5 disabled:cursor-not-allowed disabled:opacity-60">
            </label>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-2 border-t border-borde pt-5 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-terciario">Cancelar</a>
            <button type="submit" class="btn btn-primario">{{ $editando ? 'Guardar cambios' : 'Registrar usuario' }}</button>
        </div>
    </form>
</x-layouts.app>
