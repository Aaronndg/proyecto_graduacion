@php $nombre = $negocio->nombreNegocio(); @endphp
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0B2452">
    <title>{{ $nombre }} · Catálogo</title>
    <meta name="description" content="Productos de {{ $nombre }}. Elija lo que quiere y envíe su pedido por WhatsApp.">
    @include('components.layouts.partials.fuentes')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- Catálogo público del negocio: elegir productos y enviar el pedido por WhatsApp. --}}
<body class="min-h-full bg-fondo font-sans text-texto antialiased">
    <header class="banda bg-marca text-white">
        <div class="mx-auto flex h-16 max-w-5xl items-center gap-3 px-4 sm:px-6">
            <x-logo-negocio :negocio="$negocio" />
            <span class="truncate text-lg font-bold">{{ $nombre }}</span>
        </div>
    </header>
    <div class="textil" aria-hidden="true"></div>

    <main class="mx-auto max-w-5xl px-4 pt-6 pb-36 sm:px-6" data-catalogo data-negocio="{{ $nombre }}" data-whatsapp="{{ $whatsapp }}">
        <x-bienvenida :titulo="$nombre" antetitulo="Catálogo">
            @if ($whatsapp)
                Elija lo que quiere y envíe su pedido por WhatsApp. {{ $nombre }} le confirmará por ahí.
            @else
                Estos son nuestros productos. Para pedir, comuníquese con {{ $nombre }}.
            @endif
        </x-bienvenida>

        @if ($productos->isEmpty())
            <x-vacio ilustracion="caja" titulo="Aún no hay productos en el catálogo" texto="Vuelva pronto." />
        @else
            <ul class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($productos as $producto)
                    <li class="flex flex-col overflow-hidden rounded-2xl bg-superficie shadow-suave" data-producto-catalogo data-nombre="{{ $producto->nombre }}" data-precio="{{ (float) $producto->precio }}">
                        <span class="block aspect-[4/3] overflow-hidden"><x-foto-producto :producto="$producto" icono="size-12" /></span>
                        <span class="flex flex-1 flex-col gap-0.5 p-3 sm:p-4">
                            <span class="font-bold leading-snug">{{ $producto->nombre }}</span>
                            @if ($producto->descripcion)<span class="meta line-clamp-2">{{ $producto->descripcion }}</span>@endif
                            <x-moneda :valor="$producto->precio" class="mt-auto pt-2 text-[17px] font-extrabold" />
                            @if ($whatsapp)
                                <span class="mt-3 flex h-10 items-stretch overflow-hidden rounded-xl border border-stone-300 bg-superficie">
                                    <button type="button" class="flex w-10 cursor-pointer items-center justify-center text-texto-2 hover:bg-superficie-2" data-menos aria-label="Quitar uno de {{ $producto->nombre }}"><x-icono nombre="menos" clase="size-4" /></button>
                                    <output class="flex flex-1 items-center justify-center border-x border-borde text-sm font-extrabold tabular-nums" data-cantidad aria-live="polite">0</output>
                                    <button type="button" class="flex w-10 cursor-pointer items-center justify-center text-marca hover:bg-marca-50" data-mas aria-label="Agregar uno de {{ $producto->nombre }}"><x-icono nombre="mas" clase="size-4" /></button>
                                </span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </main>

    {{-- Barra del pedido: aparece al elegir algo --}}
    @if ($whatsapp)
        <div class="fixed inset-x-0 bottom-0 z-30 hidden bg-superficie/95 pb-[env(safe-area-inset-bottom)] shadow-[0_-4px_18px_rgb(11_36_82/0.12)] backdrop-blur" data-barra-pedido>
            <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
                <div>
                    <p class="meta" data-resumen-cantidad></p>
                    <p class="text-lg font-extrabold tabular-nums" data-resumen-total></p>
                </div>
                <a href="#" target="_blank" rel="noopener" class="btn btn-whatsapp" data-enviar-pedido>
                    <x-icono nombre="whatsapp" clase="size-5" /> Enviar pedido
                </a>
            </div>
        </div>
    @endif

    <footer class="px-4 pb-8 text-center text-[13px] text-texto-2">Catálogo hecho con <b>NEXO</b></footer>
</body>
</html>
