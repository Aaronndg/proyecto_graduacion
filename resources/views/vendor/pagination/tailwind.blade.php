{{-- Paginación del Design System: reemplaza la vista predeterminada de Laravel. --}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación" class="flex flex-wrap items-center justify-between gap-3">
        <p class="meta">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        </p>

        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="btn btn-secundario btn-chico opacity-50" aria-disabled="true">
                    <x-icono nombre="flecha-izquierda" clase="size-4" /> Anterior
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-secundario btn-chico">
                    <x-icono nombre="flecha-izquierda" clase="size-4" /> Anterior
                </a>
            @endif

            <span class="meta px-1 tabular-nums" aria-current="page">Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-secundario btn-chico">
                    Siguiente <x-icono nombre="flecha-derecha" clase="size-4" />
                </a>
            @else
                <span class="btn btn-secundario btn-chico opacity-50" aria-disabled="true">
                    Siguiente <x-icono nombre="flecha-derecha" clase="size-4" />
                </span>
            @endif
        </div>
    </nav>
@endif
