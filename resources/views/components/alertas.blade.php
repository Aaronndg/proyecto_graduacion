{{-- Retroalimentación de las acciones realizadas (5.5.1). --}}
@if (session('exito'))
    <div role="status" class="mb-4 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <x-icono nombre="ok" clase="size-5 shrink-0" />
        <span>{{ session('exito') }}</span>
    </div>
@endif

@if (session('error'))
    <div role="alert" class="mb-4 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <x-icono nombre="alerta" clase="size-5 shrink-0" />
        <span>{{ session('error') }}</span>
    </div>
@endif
