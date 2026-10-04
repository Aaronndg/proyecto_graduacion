@php $errorContrasena = $errors->getBag('contrasena')->any(); @endphp
<x-layouts.app titulo="Mi perfil" :subtitulo="$usuario->correo">
    <div class="max-w-xl space-y-6">
        <section class="panel p-5 sm:p-6" aria-labelledby="titulo-datos">
            <h2 id="titulo-datos" class="titulo-seccion mb-4">Mis datos</h2>

            <form method="POST" action="{{ route('perfil.update') }}" class="space-y-5" novalidate data-envio-unico>
                @csrf
                @method('PUT')
                <x-campo nombre="nombre" etiqueta="Nombre" :valor="$usuario->nombre" autocomplete="name" maxlength="100" requerido />
                @if ($usuario->esEmprendedor())
                    <x-campo nombre="negocio" etiqueta="Nombre del negocio" :valor="$usuario->negocio" autocomplete="organization" maxlength="100" requerido
                             ayuda="Así lo verán sus clientes, p. ej. en la invitación por WhatsApp." />
                @endif
                <x-campo nombre="correo" etiqueta="Correo electrónico" tipo="email" :valor="$usuario->correo" autocomplete="email" maxlength="150" requerido
                         ayuda="Con este correo inicia sesión." />
                <div class="border-t border-borde pt-5">
                    <button type="submit" class="btn btn-primario w-full sm:w-auto">Guardar cambios</button>
                </div>
            </form>
        </section>

        {{-- Contraseña: se despliega solo cuando se quiere cambiar (o si hubo un error al hacerlo) --}}
        <section class="panel p-5 sm:p-6" aria-labelledby="titulo-contrasena">
            <details class="group" @if ($errorContrasena) open @endif>
                <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-3 [&::-webkit-details-marker]:hidden">
                    <span>
                        <span id="titulo-contrasena" class="titulo-seccion block">Contraseña</span>
                        <span class="meta block">Use al menos 8 caracteres, con letras y números.</span>
                    </span>
                    <span class="btn btn-secundario group-open:hidden">Cambiar contraseña</span>
                </summary>

                <form method="POST" action="{{ route('perfil.contrasena') }}" class="mt-5 space-y-5" novalidate data-envio-unico>
                    @csrf
                    @method('PUT')
                    <x-campo nombre="contrasena_actual" etiqueta="Contraseña actual" tipo="password" autocomplete="current-password" bolsa="contrasena" requerido />
                    <x-campo nombre="contrasena" id="contrasena_nueva" etiqueta="Nueva contraseña" tipo="password" autocomplete="new-password" bolsa="contrasena" requerido />
                    <x-campo nombre="contrasena_confirmation" etiqueta="Repita la nueva contraseña" tipo="password" autocomplete="new-password" bolsa="contrasena" requerido />
                    <div class="border-t border-borde pt-5">
                        <button type="submit" class="btn btn-secundario w-full sm:w-auto" data-texto-envio="Actualizando…">Actualizar contraseña</button>
                    </div>
                </form>
            </details>
        </section>
    </div>
</x-layouts.app>
