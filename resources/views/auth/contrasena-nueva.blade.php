<x-layouts.invitado titulo="Cree su contraseña nueva" subtitulo="Use al menos 8 caracteres, con letras y números.">
    <form method="POST" action="{{ route('contrasena.guardar') }}" class="space-y-5" novalidate data-envio-unico>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-campo nombre="correo" etiqueta="Correo electrónico" tipo="email" :valor="$correo" autocomplete="email" requerido />
        <x-campo nombre="contrasena" etiqueta="Contraseña nueva" tipo="password" autocomplete="new-password" requerido autofocus />
        <x-campo nombre="contrasena_confirmation" etiqueta="Repita la contraseña nueva" tipo="password" autocomplete="new-password" requerido />
        <button type="submit" class="btn btn-primario w-full" data-texto-envio="Guardando…">Guardar contraseña</button>
    </form>

    <p class="mt-8 border-t border-borde pt-6 text-sm text-texto-2">
        ¿El enlace no funciona? <a href="{{ route('contrasena.olvido') }}" class="enlace">Pida uno nuevo</a>
    </p>
</x-layouts.invitado>
