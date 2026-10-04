@props(['etiqueta' => 'Más acciones', 'texto' => null])
{{-- Menú «⋯» para acciones secundarias de una página o fila. <details> nativo: funciona con teclado
     y sin JavaScript; app.js lo cierra al hacer clic fuera o al pulsar Escape.
     Uso: <x-menu-acciones><a class="menu-opcion" …>Editar</a></x-menu-acciones> --}}
<details {{ $attributes->class('menu relative') }} data-menu>
    <summary class="btn btn-secundario {{ $texto ? '' : 'px-2.5' }}" aria-label="{{ $etiqueta }}" title="{{ $etiqueta }}">
        @if ($texto){{ $texto }}@endif
        <x-icono nombre="puntos" clase="size-5" />
    </summary>
    <div class="menu-lista">
        {{ $slot }}
    </div>
</details>
