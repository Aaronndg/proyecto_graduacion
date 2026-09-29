<x-layouts.app titulo="Mi perfil">
    <div class="grid gap-6 lg:grid-cols-2">
        <section class="tarjeta p-6">
            <h2 class="text-base font-semibold text-slate-900">Datos de la cuenta</h2>
            <p class="mb-5 text-sm text-slate-500">Rol: {{ $usuario->rol->nombre }}</p>

            <form method="POST" action="{{ route('perfil.update') }}" class="space-y-4" novalidate>
                @csrf
                @method('PUT')
                <x-campo nombre="nombre" etiqueta="Nombre completo" :valor="$usuario->nombre" requerido />
                @if ($usuario->esEmprendedor())
                    <x-campo nombre="negocio" etiqueta="Nombre del negocio" :valor="$usuario->negocio" requerido />
                @endif
                <x-campo nombre="correo" etiqueta="Correo electrónico" tipo="email" :valor="$usuario->correo" requerido />
                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primario">Guardar cambios</button>
                </div>
            </form>
        </section>

        <section class="tarjeta p-6">
            <h2 class="mb-5 text-base font-semibold text-slate-900">Cambiar contraseña</h2>

            <form method="POST" action="{{ route('perfil.contrasena') }}" class="space-y-4" novalidate>
                @csrf
                @method('PUT')
                <x-campo nombre="contrasena_actual" etiqueta="Contraseña actual" tipo="password" autocomplete="current-password" bolsa="contrasena" requerido />
                <x-campo nombre="contrasena" id="contrasena_nueva" etiqueta="Nueva contraseña" tipo="password" autocomplete="new-password" bolsa="contrasena" requerido ayuda="Mínimo 8 caracteres, con letras y números." />
                <x-campo nombre="contrasena_confirmation" etiqueta="Confirmar nueva contraseña" tipo="password" autocomplete="new-password" bolsa="contrasena" requerido />
                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primario">Actualizar contraseña</button>
                </div>
            </form>
        </section>
    </div>
</x-layouts.app>
