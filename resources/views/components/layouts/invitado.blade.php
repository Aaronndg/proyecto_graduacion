@props(['titulo', 'subtitulo' => null, 'pestana' => null])
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0D0E11">
    <title>{{ $pestana ?? $titulo }} · {{ config('app.name') }}</title>
    @include('components.layouts.partials.fuentes')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- Acceso: franja azul con la marca y una sola tarjeta con lo necesario para entrar o crear la cuenta. --}}
<body class="flex min-h-full flex-col bg-fondo font-sans text-texto antialiased">
    <header class="banda bg-noche/90 border-b border-white/[0.06] backdrop-blur text-white">
        <div class="mx-auto flex h-16 max-w-5xl items-center px-4 sm:px-6">
            <a href="{{ route('login') }}" class="rounded-xl" aria-label="NEXO, ir a iniciar sesión"><x-marca claro /></a>
        </div>
    </header>
    <div class="textil" aria-hidden="true"></div>

    <main class="mx-auto flex w-full max-w-[28rem] flex-1 flex-col justify-center px-4 py-10">
        <div class="panel p-6 sm:p-8">
            <h1 class="text-[28px] leading-tight font-bold text-balance">{{ $titulo }}</h1>
            @if ($subtitulo)<p class="mt-1 text-texto-2">{{ $subtitulo }}</p>@endif

            <div class="mt-7">
                <x-alertas />
                {{ $slot }}
            </div>
        </div>
    </main>

    <footer class="px-4 pb-6 text-center text-[13px] text-texto-2">Universidad Mariano Gálvez · Sede Jutiapa</footer>
</body>
</html>
