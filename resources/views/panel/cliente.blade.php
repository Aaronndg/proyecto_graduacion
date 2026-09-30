<x-layouts.app titulo="Mis pedidos">
    <div class="mb-6">
        <p class="text-slate-500">Hola, <span class="font-medium text-slate-700">{{ auth()->user()->nombre }}</span>. Consulte aquí el estado de sus pedidos.</p>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <x-estadistica titulo="Pedidos en curso" :valor="$enCurso" icono="reloj" color="bg-amber-50 text-amber-600" />
        <x-estadistica titulo="Total de pedidos" :valor="$pedidos->count()" icono="pedido" />

        {{-- Vinculación con el código entregado por el negocio (RN-06) --}}
        <form method="POST" action="{{ route('vincular') }}" class="tarjeta p-4" novalidate data-envio-unico>
            @csrf
            <label for="codigo" class="etiqueta">¿Tiene un código de un negocio?</label>
            <div class="flex gap-2">
                <input id="codigo" name="codigo" value="{{ old('codigo') }}" placeholder="K7QM-4XPA" autocomplete="off" maxlength="20"
                       @class(['campo uppercase', 'campo-error' => $errors->has('codigo')])
                       @error('codigo') aria-invalid="true" aria-describedby="codigo-error" @enderror>
                <button type="submit" class="btn btn-primario shrink-0">Vincular</button>
            </div>
            @error('codigo')
                <p id="codigo-error" class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @else
                <p class="mt-1 text-xs text-slate-500">Ingréselo para ver sus pedidos de ese negocio.</p>
            @enderror
        </form>
    </div>

    <section class="tarjeta mt-6">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Mis pedidos</h2>
        </div>

        @if ($pedidos->isEmpty())
            <div class="px-5 py-12 text-center">
                <x-icono nombre="pedido" clase="mx-auto size-10 text-slate-300" />
                <p class="mt-3 font-medium text-slate-700">Todavía no tiene pedidos asociados</p>
                <p class="mt-1 text-sm text-slate-500">Pida su código de vinculación al negocio donde hizo su pedido e ingréselo arriba.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="tabla">
                    <thead>
                        <tr><th>No.</th><th>Negocio</th><th>Fecha</th><th>Estado</th><th class="text-right">Total</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($pedidos as $pedido)
                            <tr>
                                <td><a href="{{ route('mis-pedidos.show', $pedido) }}" class="font-medium text-marca-600 hover:underline">#{{ $pedido->numero() }}</a></td>
                                <td>{{ $pedido->emprendedor->negocio ?? $pedido->emprendedor->nombre }}</td>
                                <td class="whitespace-nowrap">{{ $pedido->fecha->format('d/m/Y') }}</td>
                                <td><x-estado-pedido :estado="$pedido->estado" /></td>
                                <td class="text-right"><x-moneda :valor="$pedido->total" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.app>
