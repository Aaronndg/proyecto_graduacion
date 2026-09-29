@props(['estado'])
{{-- El estado se identifica con texto, no solo con color (5.5.4). --}}
<span class="insignia {{ $estado->color() }}">
    <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ $estado->nombre }}
</span>
