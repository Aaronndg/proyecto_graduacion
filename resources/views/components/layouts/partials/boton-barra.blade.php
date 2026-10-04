{{-- Un acceso de la barra inferior del teléfono: ícono + texto, área táctil de 64 px de alto. --}}
<li class="flex flex-1">
    <a href="{{ route($item['ruta']) }}" @if ($item['actual']) aria-current="page" @endif
       @class([
           'flex min-h-16 w-full flex-col items-center justify-center gap-1 px-2 text-[11px] font-medium',
           'text-marca' => $item['actual'],
           'text-texto-2' => ! $item['actual'],
       ])>
        <x-icono :nombre="$item['icono']" clase="size-6" />
        {{ $item['texto'] }}
    </a>
</li>
