@props(['titulo', 'valor', 'icono', 'color' => 'bg-marca-50 text-marca-600', 'enlace' => null])
@php $etiqueta = $enlace ? 'a' : 'div'; @endphp
<{{ $etiqueta }} @if ($enlace) href="{{ $enlace }}" @endif
    @class(['tarjeta flex items-center gap-4 p-5', 'transition hover:-translate-y-0.5 hover:shadow-md' => $enlace])>
    <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl {{ $color }}">
        <x-icono :nombre="$icono" clase="size-6" />
    </div>
    <div class="min-w-0">
        <p class="truncate text-sm font-medium text-stone-500">{{ $titulo }}</p>
        <p class="mt-0.5 text-2xl font-bold tracking-tight text-stone-900 tabular-nums">{{ $valor }}</p>
    </div>
</{{ $etiqueta }}>
