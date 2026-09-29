<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $codigo }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-full items-center justify-center bg-slate-100 px-4 font-sans text-slate-800">
    <main class="tarjeta max-w-md p-8 text-center">
        <p class="text-5xl font-bold text-marca-600">{{ $codigo }}</p>
        <h1 class="mt-3 text-xl font-semibold text-slate-900">{{ $titulo }}</h1>
        <p class="mt-2 text-slate-500">{{ $mensaje }}</p>
        <a href="{{ url('/panel') }}" class="btn btn-primario mt-6">Volver al inicio</a>
    </main>
</body>
</html>
