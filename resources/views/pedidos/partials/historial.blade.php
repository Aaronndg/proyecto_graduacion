{{-- Historial de estados del pedido (RF-11): el más reciente arriba, con quién y cuándo. --}}
<h2 id="{{ $idTitulo }}" class="titulo-seccion mb-3">Historial</h2>
<ol class="space-y-4 border-l border-borde pl-4">
    @foreach ($pedido->historial->reverse() as $registro)
        <li class="relative">
            <span @class(['absolute top-1.5 -left-[21px] size-2.5 rounded-full ring-4 ring-fondo', 'bg-marca' => $loop->first, 'bg-stone-300' => ! $loop->first]) aria-hidden="true"></span>
            <p class="text-sm"><span class="font-medium">{{ $registro->estado->nombre }}</span> · <span class="text-texto-2">{{ $registro->fecha_hora->translatedFormat('j M, H:i') }}</span></p>
            @if ($registro->observacion)<p class="text-sm text-texto">{{ $registro->observacion }}</p>@endif
            @if ($registro->usuario)<p class="meta">Por {{ $registro->usuario->nombre }}</p>@endif
        </li>
    @endforeach
</ol>
