@props(['titulo', 'valor', 'icono', 'color' => 'bg-marca-50 text-marca-600'])
<div class="tarjeta flex items-center gap-4 p-5">
    <div class="flex size-12 shrink-0 items-center justify-center rounded-full {{ $color }}">
        <x-icono :nombre="$icono" clase="size-6" />
    </div>
    <div class="min-w-0">
        <p class="truncate text-sm text-slate-500">{{ $titulo }}</p>
        <p class="text-2xl font-bold text-slate-900 tabular-nums">{{ $valor }}</p>
    </div>
</div>
