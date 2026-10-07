@props(['nombre', 'tamano' => 'md'])
@php
    // Color fijo por persona (derivado del nombre), para reconocer a cada cliente de un vistazo.
    $tonos = [
        'bg-oro-suave text-oro-texto',
        'bg-marca-100 text-marca',
        'bg-emerald-50 text-emerald-700',
        'bg-red-50 text-red-700',
        'bg-[#221E33] text-[#B9A8F0]',
        'bg-[#132A2E] text-[#7FD3DF]',
    ];
    $palabras = preg_split('/\s+/', trim($nombre));
    $iniciales = mb_strtoupper(mb_substr($palabras[0] ?? '', 0, 1).mb_substr(count($palabras) > 1 ? end($palabras) : '', 0, 1));
    $medidas = ['sm' => 'size-8 text-[12px]', 'md' => 'size-10 text-[13px]', 'lg' => 'size-12 text-[15px]'][$tamano];
@endphp
<span {{ $attributes->class(['inline-flex shrink-0 items-center justify-center rounded-full font-extrabold', $medidas, $tonos[crc32($nombre) % count($tonos)]]) }} aria-hidden="true">{{ $iniciales }}</span>
