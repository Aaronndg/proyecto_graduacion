{{-- Retroalimentación de las acciones realizadas (5.5.1). --}}
@if (session('exito'))
    <div role="status" class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-sm font-medium text-emerald-800">
        <x-icono nombre="ok" clase="size-5 shrink-0 text-emerald-600" />
        <span>{{ session('exito') }}</span>
    </div>
@endif

@if (session('error'))
    <div role="alert" class="mb-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm font-medium text-red-800">
        <x-icono nombre="alerta" clase="size-5 shrink-0 text-red-600" />
        <span>{{ session('error') }}</span>
    </div>
@endif
