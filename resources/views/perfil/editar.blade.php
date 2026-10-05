@php $errorContrasena = $errors->getBag('contrasena')->any(); @endphp
<x-layouts.app titulo="Mi perfil" :subtitulo="$usuario->correo">
    <div class="max-w-xl space-y-6">
        <section class="panel p-5 sm:p-6" aria-labelledby="titulo-datos">
            <h2 id="titulo-datos" class="titulo-seccion mb-4">Mis datos</h2>

            <form method="POST" action="{{ route('perfil.update') }}" class="space-y-5" enctype="multipart/form-data" novalidate data-envio-unico>
                @csrf
                @method('PUT')
                <x-campo nombre="nombre" etiqueta="Nombre" :valor="$usuario->nombre" autocomplete="name" maxlength="100" requerido />
                @if ($usuario->esEmprendedor())
                    <x-campo nombre="negocio" etiqueta="Nombre del negocio" :valor="$usuario->negocio" autocomplete="organization" maxlength="100" requerido
                             ayuda="Así lo verán sus clientes, p. ej. en la invitación por WhatsApp." />
                    <x-campo nombre="telefono" etiqueta="WhatsApp del negocio (opcional)" tipo="tel" :valor="$usuario->telefono" placeholder="5555-5555" inputmode="tel" maxlength="20"
                             ayuda="Sus clientes podrán escribirle con un toque desde «Mis pedidos»." />

                    {{-- Logo del negocio (opcional): vista previa inmediata al elegirlo --}}
                    <div data-foto>
                        <span class="etiqueta">Logo del negocio <span class="font-normal text-texto-2">(opcional)</span></span>
                        <div class="flex items-center gap-4">
                            <span class="size-20 shrink-0 overflow-hidden rounded-2xl bg-white shadow-[0_0_0_1px_var(--color-borde)]">
                                <img src="{{ $usuario->urlLogo() }}" alt="" @class(['size-full object-contain', 'hidden' => ! $usuario->logo]) data-foto-vista>
                                <span @class(['flex size-full items-center justify-center', 'hidden' => $usuario->logo]) data-foto-vacia><x-avatar :nombre="$usuario->nombreNegocio()" tamano="lg" /></span>
                            </span>
                            <div class="min-w-0 space-y-2">
                                <label for="logo" class="btn btn-secundario btn-chico cursor-pointer">
                                    <x-icono nombre="imagen" clase="size-4" /> {{ $usuario->logo ? 'Cambiar logo' : 'Subir logo' }}
                                </label>
                                <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" data-foto-archivo>
                                <p class="ayuda mt-0">Lo verán sus clientes junto a sus pedidos. JPG, PNG o WebP, hasta 2 MB.</p>
                                @if ($usuario->logo)
                                    <label class="flex cursor-pointer items-center gap-2 text-sm text-texto-2">
                                        <input type="checkbox" name="quitar_logo" value="1" class="casilla" data-foto-quitar> Quitar el logo
                                    </label>
                                @endif
                            </div>
                        </div>
                        @error('logo')<p class="error-campo">{{ $message }}</p>@enderror
                    </div>
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
