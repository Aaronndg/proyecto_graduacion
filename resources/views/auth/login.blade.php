<x-layouts.invitado titulo="Bienvenido" subtitulo="Ingrese con su correo y contraseña.">
    <form method="POST" action="{{ route('login.store') }}" class="space-y-5" novalidate data-envio-unico>
        @csrf
        <x-campo nombre="correo" etiqueta="Correo electrónico" tipo="email" autocomplete="username" placeholder="nombre@correo.com" requerido autofocus />
        <x-campo nombre="contrasena" etiqueta="Contraseña" tipo="password" autocomplete="current-password" requerido />

        <button type="submit" class="btn btn-primario w-full py-3 text-base">Iniciar sesión</button>
    </form>

    <p class="mt-8 text-center text-sm text-stone-600">
        ¿Todavía no tiene cuenta?
        <a href="{{ route('registro') }}" class="enlace">Crear una cuenta</a>
    </p>
</x-layouts.invitado>
