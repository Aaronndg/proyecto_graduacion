<x-layouts.invitado titulo="Inicio de sesión">
    <form method="POST" action="{{ route('login.store') }}" class="space-y-5" novalidate>
        @csrf
        <x-campo nombre="correo" etiqueta="Correo electrónico" tipo="email" autocomplete="username" placeholder="Ingrese su correo electrónico" requerido autofocus />
        <x-campo nombre="contrasena" etiqueta="Contraseña" tipo="password" autocomplete="current-password" requerido />

        <button type="submit" class="btn btn-primario w-full py-2.5">Iniciar sesión</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        ¿No tiene cuenta?
        <a href="{{ route('registro') }}" class="font-semibold text-marca-600 hover:underline">Crear una cuenta</a>
    </p>
</x-layouts.invitado>
