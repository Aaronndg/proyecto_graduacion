@props(['nombre', 'clase' => 'size-7'])
{{-- Íconos de dos tonos (relleno suave + trazo azul/oro) para los accesos en círculos. --}}
<svg class="{{ $clase }}" viewBox="0 0 24 24" aria-hidden="true">
    @switch($nombre)
        @case('pedido')
            <rect x="4" y="3" width="16" height="18" rx="3" fill="#E8CD8F"/><path d="M8 3h8v3H8z" fill="var(--color-oro)"/><path d="M12 9v8M8 13h8" stroke="var(--color-marca)" stroke-width="2" stroke-linecap="round"/>
            @break
        @case('clientes')
            <circle cx="9" cy="8" r="4" fill="var(--color-marca-300)"/><path d="M2 21a7 7 0 0 1 14 0z" fill="var(--color-marca-300)"/><circle cx="17" cy="9" r="3" fill="var(--color-marca)"/><path d="M13 21a5 5 0 0 1 10 0z" fill="var(--color-marca)"/>
            @break
        @case('productos')
            <path d="M21 8 12 3 3 8v8l9 5 9-5V8Z" fill="#E8CD8F"/><path d="M3 8l9 5 9-5-9-5z" fill="var(--color-oro)"/><path d="M12 13v8" stroke="var(--color-marca)" stroke-width="2"/>
            @break
        @case('reportes')
            <rect x="3" y="12" width="4" height="9" rx="1.5" fill="var(--color-marca-300)"/><rect x="10" y="7" width="4" height="14" rx="1.5" fill="var(--color-marca)"/><rect x="17" y="3" width="4" height="18" rx="1.5" fill="var(--color-oro)"/>
            @break
        @case('negocio')
            <path d="M3 9h18v12H3z" fill="#E8CD8F"/><path d="M2 9l2-5h16l2 5z" fill="var(--color-oro)"/><rect x="9" y="13" width="6" height="8" rx="1" fill="var(--color-marca)"/>
            @break
        @case('usuario')
            <circle cx="12" cy="8" r="4.5" fill="var(--color-marca-300)"/><path d="M3.5 21a8.5 8.5 0 0 1 17 0z" fill="var(--color-marca)"/>
            @break
    @endswitch
</svg>
