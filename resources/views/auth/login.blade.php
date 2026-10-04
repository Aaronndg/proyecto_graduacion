<x-layouts.invitado titulo="Iniciar sesión" subtitulo="Ingrese con su correo y contraseña.">
    <form method="POST" action="{{ route('login.store') }}" class="space-y-5" novalidate data-envio-unico>
        @csrf
        <x-campo nombre="correo" etiqueta="Correo electrónico" tipo="email" autocomplete="username" placeholder="nombre@correo.com" requerido autofocus />
        <x-campo nombre="contrasena" etiqueta="Contraseña" tipo="password" autocomplete="current-password" requerido />

        <button type="submit" class="btn btn-primario w-full" data-texto-envio="Ingresando…">Iniciar sesión</button>
    </form>

    <p class="mt-8 border-t border-borde pt-6 text-sm text-texto-2">
        ¿No tiene cuenta? <a href="{{ route('registro') }}" class="enlace">Crear una cuenta</a>
    </p>
</x-layouts.invitado>
