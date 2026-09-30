@props(['icono' => 'pedido', 'titulo', 'texto' => null])
<div class="px-5 py-12 text-center">
    <x-icono :nombre="$icono" clase="mx-auto size-10 text-slate-300" />
    <p class="mt-3 font-medium text-slate-700">{{ $titulo }}</p>
    @if ($texto)<p class="mt-1 text-sm text-slate-500">{{ $texto }}</p>@endif
    @if ($slot->isNotEmpty())<div class="mt-4">{{ $slot }}</div>@endif
</div>
