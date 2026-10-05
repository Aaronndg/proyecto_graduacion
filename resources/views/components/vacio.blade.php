@props(['icono' => 'pedido', 'titulo', 'texto' => null, 'ilustracion' => 'libreta'])
{{-- Estado vacío: ilustración propia, un título y la acción para salir de él. --}}
<div class="flex flex-col items-center px-5 py-10 text-center">
    <x-ilustracion :nombre="$ilustracion" class="mb-3" />
    <p class="font-bold">{{ $titulo }}</p>
    @if ($texto)<p class="mx-auto mt-1 max-w-sm text-sm text-texto-2">{{ $texto }}</p>@endif
    @if ($slot->isNotEmpty())<div class="mt-5">{{ $slot }}</div>@endif
</div>
