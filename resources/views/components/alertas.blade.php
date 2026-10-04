@props(['separacion' => 'bottom-6'])
{{-- Retroalimentación de las acciones realizadas (5.5.1).
     Error: en línea, donde el usuario está mirando. Éxito: aviso flotante que se retira solo. --}}
@if (session('error'))
    <div role="alert" class="mb-6 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <x-icono nombre="alerta" clase="size-5 shrink-0 text-red-600" />
        <span>{{ session('error') }}</span>
    </div>
@endif

@if (session('exito'))
    <div class="pointer-events-none fixed inset-x-0 {{ $separacion }} z-50 flex justify-center px-4 lg:justify-end lg:px-8">
        <div role="status" data-aviso
             class="aviso pointer-events-auto flex max-w-md items-start gap-3 rounded-lg bg-stone-900 py-3 pr-2 pl-4 text-sm text-stone-50 shadow-flotante">
            <x-icono nombre="ok" clase="mt-px size-5 shrink-0 text-marca-300" />
            <span class="flex-1">{{ session('exito') }}</span>
            <button type="button" data-cerrar-aviso class="-my-1 cursor-pointer rounded-md p-1 text-stone-300 hover:bg-white/10 hover:text-white">
                <x-icono nombre="cerrar" clase="size-4" />
                <span class="sr-only">Cerrar aviso</span>
            </button>
        </div>
    </div>
@endif
