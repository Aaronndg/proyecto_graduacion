@props(['titulo'])
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-gradient-to-br from-marca-50 via-white to-slate-100 px-4 py-10 font-sans text-slate-800 antialiased">
    <main class="w-full max-w-md">
        <div class="mb-6 flex flex-col items-center text-center">
            <span class="flex size-14 items-center justify-center rounded-2xl bg-marca-600 text-white shadow-lg shadow-marca-600/30">
                <x-icono nombre="producto" clase="size-8" />
            </span>
            <p class="mt-3 text-sm font-bold tracking-widest text-marca-900 uppercase">Sistema de pedidos</p>
            <p class="text-xs text-slate-500">Emprendedores de Jutiapa</p>
        </div>

        <div class="tarjeta p-6 sm:p-8">
            <h1 class="mb-6 text-center text-2xl font-bold text-slate-900">{{ $titulo }}</h1>
            <x-alertas />
            {{ $slot }}
        </div>
    </main>
</body>
</html>
