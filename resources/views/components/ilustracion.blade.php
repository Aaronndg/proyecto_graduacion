@props(['nombre' => 'libreta'])
{{-- Ilustraciones propias para estados vacíos, en la paleta de NEXO. --}}
<svg {{ $attributes->class('h-24 w-32') }} viewBox="0 0 120 90" aria-hidden="true">
    @switch($nombre)
        @case('caja')
            <ellipse cx="60" cy="80" rx="42" ry="5" fill="var(--color-marca-100)"/>
            <path d="M20 34 60 18l40 16v34L60 84 20 68Z" fill="#F6EBD2"/><path d="M20 34 60 50l40-16-40-16Z" fill="#E8CD8F"/><path d="M60 50v34" stroke="#C9A65A" stroke-width="2"/>
            <path d="m40 26 40 16v10l-8 3V45L32 29Z" fill="var(--color-oro)"/>
            <circle cx="96" cy="20" r="11" fill="var(--color-marca)"/><path d="M96 15v10M91 20h10" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/>
            @break
        @case('clientes')
            <ellipse cx="60" cy="80" rx="42" ry="5" fill="var(--color-marca-100)"/>
            <circle cx="44" cy="34" r="13" fill="var(--color-marca-300)"/><path d="M20 78a24 24 0 0 1 48 0z" fill="var(--color-marca-300)"/>
            <circle cx="78" cy="38" r="11" fill="var(--color-marca)"/><path d="M58 78a20 20 0 0 1 40 0z" fill="var(--color-marca)"/>
            <circle cx="96" cy="18" r="9" fill="var(--color-oro)"/><path d="M96 14v8M92 18h8" stroke="#0A1E45" stroke-width="2.4" stroke-linecap="round"/>
            @break
        @default
            <ellipse cx="60" cy="82" rx="42" ry="5" fill="var(--color-marca-100)"/>
            <rect x="30" y="14" width="52" height="66" rx="8" fill="var(--color-marca-100)"/>
            <rect x="42" y="8" width="28" height="12" rx="5" fill="var(--color-oro)"/>
            <path d="M42 38h28M42 50h28M42 62h18" stroke="var(--color-marca-300)" stroke-width="5" stroke-linecap="round"/>
            <path d="m84 30 18-18 8 8-18 18-11 3Z" fill="var(--color-oro)"/><path d="m98 16 8 8" stroke="#A87A1A" stroke-width="2"/>
    @endswitch
</svg>
