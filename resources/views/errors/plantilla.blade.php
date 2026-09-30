<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titulo }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-full flex-col items-center justify-center bg-stone-50 px-5 font-sans text-stone-800 antialiased">
    <x-marca class="mb-10" />
    <main class="tarjeta w-full max-w-md p-8 text-center sm:p-10">
        <p class="text-sm font-semibold tracking-widest text-marca-600">ERROR {{ $codigo }}</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-stone-900">{{ $titulo }}</h1>
        <p class="mt-2 text-stone-500">{{ $mensaje }}</p>
        <a href="{{ url('/panel') }}" class="btn btn-primario mt-8">Volver al inicio</a>
    </main>
</body>
</html>
