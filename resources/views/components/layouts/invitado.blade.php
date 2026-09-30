@props(['titulo', 'subtitulo' => null])
<!DOCTYPE html>
<html lang="es" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a8076">
    <title>{{ $titulo }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-stone-800 antialiased">
    <div class="flex min-h-full">
        {{-- Panel de marca (pantallas grandes) --}}
        <aside class="relative hidden w-[44%] max-w-xl overflow-hidden bg-gradient-to-br from-marca-700 via-marca-800 to-marca-950 p-12 text-white lg:flex lg:flex-col">
            <div class="pointer-events-none absolute -top-24 -right-24 size-96 rounded-full bg-marca-400/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-20 size-96 rounded-full bg-marca-300/10 blur-3xl"></div>

            <x-marca claro class="relative" />

            <div class="relative mt-auto">
                <h2 class="text-4xl leading-tight font-bold tracking-tight">Sus pedidos,<br>en orden y a la vista.</h2>
                <p class="mt-4 max-w-sm text-marca-100/90">Una herramienta sencilla para los emprendedores de Jutiapa y sus clientes.</p>

                <ul class="mt-10 space-y-4 text-sm">
                    @foreach ([
                        'Registre pedidos en segundos, sin perder información.',
                        'Sus clientes ven el estado de su pedido desde el celular.',
                        'Historial y ventas siempre disponibles.',
                    ] as $beneficio)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-white/15">
                                <svg class="size-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                            </span>
                            <span class="text-marca-50">{{ $beneficio }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="relative mt-12 text-xs text-marca-100/60">Universidad Mariano Gálvez · Sede Jutiapa</p>
        </aside>

        {{-- Formulario --}}
        <main class="flex flex-1 flex-col items-center justify-center px-5 py-10 sm:px-10">
            <div class="w-full max-w-md">
                <x-marca class="mb-10 lg:hidden" />

                <h1 class="text-3xl font-bold tracking-tight text-stone-900">{{ $titulo }}</h1>
                @if ($subtitulo)<p class="mt-2 text-stone-500">{{ $subtitulo }}</p>@endif

                <div class="mt-8">
                    <x-alertas />
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>
</body>
</html>
