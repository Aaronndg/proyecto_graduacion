@php
    // El botón lleva al lugar de cada quien: Hoy (emprendedor), Mis pedidos (cliente), inicio (admin) o iniciar sesión.
    $usuario = rescue(fn () => auth()->user(), null, false);
    [$destino, $boton] = match (true) {
        ! $usuario => [route('login'), 'Iniciar sesión'],
        $usuario->esEmprendedor() => [route('panel'), 'Volver a Hoy'],
        $usuario->esCliente() => [route('panel'), 'Volver a Mis pedidos'],
        default => [route('panel'), 'Volver al inicio'],
    };
@endphp
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F7F7F5">
    <title>{{ $titulo }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-full flex-col bg-fondo font-sans text-texto antialiased">
    <main class="mx-auto flex w-full max-w-[25rem] flex-1 flex-col justify-center px-4 py-10">
        <x-marca class="mb-10 self-start" />
        <h1 class="text-2xl font-semibold tracking-tight text-balance">{{ $titulo }}</h1>
        <p class="mt-2 text-texto-2">{{ $mensaje }}</p>
        <a href="{{ $destino }}" class="btn btn-primario mt-8 self-start">{{ $boton }}</a>
        <p class="meta mt-10">Código de error: {{ $codigo }}</p>
    </main>
</body>
</html>
