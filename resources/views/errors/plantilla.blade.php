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
    <meta name="theme-color" content="#102945">
    <title>{{ $titulo }} · {{ config('app.name') }}</title>
    @include('components.layouts.partials.fuentes')
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-full flex-col bg-fondo font-sans text-texto antialiased">
    <header class="banda bg-marca text-white">
        <div class="mx-auto flex h-16 max-w-5xl items-center px-4 sm:px-6"><x-marca claro /></div>
    </header>
    <div class="textil" aria-hidden="true"></div>
    <main class="mx-auto flex w-full max-w-[28rem] flex-1 flex-col justify-center px-4 py-10">
        <div class="panel p-6 sm:p-8">
            <p class="text-sm font-extrabold text-oro-texto">Código de error {{ $codigo }}</p>
            <h1 class="mt-1 font-display text-[28px] leading-tight font-semibold text-balance">{{ $titulo }}</h1>
            <p class="mt-2 text-texto-2">{{ $mensaje }}</p>
            <a href="{{ $destino }}" class="btn btn-primario mt-7">{{ $boton }}</a>
        </div>
    </main>
</body>
</html>
