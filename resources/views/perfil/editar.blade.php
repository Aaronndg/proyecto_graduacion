<x-layouts.app titulo="Mi perfil" :subtitulo="'Cuenta de '.mb_strtolower($usuario->rol->nombre).'.'">
    <div class="grid max-w-5xl grid-cols-1 gap-6 lg:grid-cols-2">
        <section class="tarjeta p-6">
            <h2 class="font-semibold text-stone-900">Mis datos</h2>
            <p class="mb-5 text-sm text-stone-500">Así le verán en el sistema.</p>

            <form method="POST" action="{{ route('perfil.update') }}" class="space-y-4" novalidate data-envio-unico>
                @csrf
                @method('PUT')
                <x-campo nombre="nombre" etiqueta="Nombre" :valor="$usuario->nombre" maxlength="100" requerido />
                @if ($usuario->esEmprendedor())
                    <x-campo nombre="negocio" etiqueta="Nombre del negocio" :valor="$usuario->negocio" maxlength="100" requerido />
                @endif
                <x-campo nombre="correo" etiqueta="Correo electrónico" tipo="email" :valor="$usuario->correo" maxlength="150" requerido />
                <button type="submit" class="btn btn-primario">Guardar cambios</button>
            </form>
        </section>

        <section class="tarjeta p-6">
            <h2 class="font-semibold text-stone-900">Cambiar contraseña</h2>
            <p class="mb-5 text-sm text-stone-500">Use al menos 8 caracteres, con letras y números.</p>

            <form method="POST" action="{{ route('perfil.contrasena') }}" class="space-y-4" novalidate data-envio-unico>
                @csrf
                @method('PUT')
                <x-campo nombre="contrasena_actual" etiqueta="Contraseña actual" tipo="password" autocomplete="current-password" bolsa="contrasena" requerido />
                <x-campo nombre="contrasena" id="contrasena_nueva" etiqueta="Nueva contraseña" tipo="password" autocomplete="new-password" bolsa="contrasena" requerido />
                <x-campo nombre="contrasena_confirmation" etiqueta="Repita la nueva contraseña" tipo="password" autocomplete="new-password" bolsa="contrasena" requerido />
                <button type="submit" class="btn btn-primario">Actualizar contraseña</button>
            </form>
        </section>
    </div>
</x-layouts.app>
