@props(['icono' => 'pedido', 'titulo', 'texto' => null])
<div class="px-5 py-14 text-center">
    <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-stone-100 text-stone-400">
        <x-icono :nombre="$icono" clase="size-7" />
    </span>
    <p class="mt-4 font-semibold text-stone-800">{{ $titulo }}</p>
    @if ($texto)<p class="mx-auto mt-1 max-w-sm text-sm text-stone-500">{{ $texto }}</p>@endif
    @if ($slot->isNotEmpty())<div class="mt-5">{{ $slot }}</div>@endif
</div>
