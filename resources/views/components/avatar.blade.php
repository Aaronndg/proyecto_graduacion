@props(['nombre', 'tamano' => 'md'])
@php
    // Color fijo por persona (derivado del nombre), para reconocer a cada cliente de un vistazo.
    $tonos = [
        'bg-linear-to-br from-[#F6E7CC] to-[#E2C690] text-[#2A1F0C]',
        'bg-linear-to-br from-[#DCE7FF] to-[#AFC6FA] text-[#0E1E44]',
        'bg-linear-to-br from-[#D4F3E4] to-[#9FDDBF] text-[#0B2C1F]',
        'bg-linear-to-br from-[#FBDCD5] to-[#EFB1A5] text-[#3D1611]',
        'bg-linear-to-br from-[#E6DCFB] to-[#C4B0F0] text-[#231646]',
        'bg-linear-to-br from-[#D3EEF2] to-[#A2D8E1] text-[#0B2C33]',
    ];
    $palabras = preg_split('/\s+/', trim($nombre));
    $iniciales = mb_strtoupper(mb_substr($palabras[0] ?? '', 0, 1).mb_substr(count($palabras) > 1 ? end($palabras) : '', 0, 1));
    $medidas = ['sm' => 'size-8 text-[12px]', 'md' => 'size-10 text-[13px]', 'lg' => 'size-12 text-[15px]'][$tamano];
@endphp
<span {{ $attributes->class(['inline-flex shrink-0 items-center justify-center rounded-full font-extrabold', $medidas, $tonos[crc32($nombre) % count($tonos)], 'shadow-[inset_0_1px_0_rgb(255_255_255/0.6),0_2px_6px_-2px_rgb(0_0_0/0.6)]']) }} aria-hidden="true">{{ $iniciales }}</span>
