<x-layouts.invitado titulo="¿Olvidó su contraseña?" subtitulo="Escriba su correo y le enviaremos un enlace para crear una nueva.">
    @if (session('enviado'))
        {{-- Mismo mensaje exista o no la cuenta, para no revelar qué correos están registrados --}}
        <div role="status" class="rounded-xl bg-emerald-50 px-4 py-4 text-sm text-emerald-800">
            <p class="font-bold">Revise su correo</p>
            <p class="mt-1">Si <b>{{ session('enviado') }}</b> tiene una cuenta en NEXO, le llegará un mensaje con un botón para crear su contraseña nueva. Puede tardar unos minutos; revise también la carpeta de correo no deseado.</p>
        </div>
        <a href="{{ route('login') }}" class="btn btn-primario mt-6 w-full">Volver a iniciar sesión</a>
    @else
        <form method="POST" action="{{ route('contrasena.enviar') }}" class="space-y-5" novalidate data-envio-unico>
            @csrf
            <x-campo nombre="correo" etiqueta="Correo electrónico" tipo="email" autocomplete="email" placeholder="nombre@correo.com" requerido autofocus />
            <button type="submit" class="btn btn-primario w-full" data-texto-envio="Enviando…">Enviarme el enlace</button>
        </form>
    @endif

    <p class="mt-8 border-t border-borde pt-6 text-sm text-texto-2">
        ¿Ya la recordó? <a href="{{ route('login') }}" class="enlace">Iniciar sesión</a>
    </p>
</x-layouts.invitado>
