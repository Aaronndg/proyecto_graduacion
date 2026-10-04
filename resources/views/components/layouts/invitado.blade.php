@props(['titulo', 'subtitulo' => null, 'pestana' => null])
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F7F7F5">
    <title>{{ $pestana ?? $titulo }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- Acceso: una sola columna, solo lo necesario para entrar o crear la cuenta. --}}
<body class="flex min-h-full flex-col bg-fondo font-sans text-texto antialiased">
    <main class="mx-auto flex w-full max-w-[25rem] flex-1 flex-col justify-center px-4 py-10">
        <a href="{{ route('login') }}" class="mb-10 self-start rounded-lg" aria-label="Pedidos Jutiapa, ir a iniciar sesión"><x-marca /></a>

        <h1 class="text-2xl font-semibold tracking-tight text-balance">{{ $titulo }}</h1>
        @if ($subtitulo)<p class="mt-1 text-texto-2">{{ $subtitulo }}</p>@endif

        <div class="mt-8">
            <x-alertas />
            {{ $slot }}
        </div>
    </main>

    <footer class="px-4 pb-6 text-center text-[13px] text-texto-2">Universidad Mariano Gálvez · Sede Jutiapa</footer>
</body>
</html>
