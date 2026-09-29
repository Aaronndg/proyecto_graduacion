<x-layouts.app titulo="Panel principal">
    <div class="mb-6">
        <p class="text-slate-500">Bienvenido, <span class="font-medium text-slate-700">{{ auth()->user()->nombre }}</span>. Resumen general de la plataforma.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-estadistica titulo="Emprendedores" :valor="$totalEmprendedores" icono="negocio" />
        <x-estadistica titulo="Clientes con cuenta" :valor="$totalClientes" icono="usuarios" color="bg-emerald-50 text-emerald-600" />
        <x-estadistica titulo="Pedidos registrados" :valor="$totalPedidos" icono="pedido" color="bg-amber-50 text-amber-600" />
        <x-estadistica titulo="Cuentas desactivadas" :valor="$totalInactivos" icono="alerta" color="bg-slate-100 text-slate-600" />
    </div>

    <section class="tarjeta mt-6">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Usuarios recientes</h2>
            <a href="{{ route('admin.usuarios.index') }}" class="text-sm font-medium text-marca-600 hover:underline">Ver todos</a>
        </div>
        <div class="overflow-x-auto">
            <table class="tabla">
                <thead>
                    <tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Registro</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($usuariosRecientes as $usuario)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $usuario->nombre }}</td>
                            <td>{{ $usuario->correo }}</td>
                            <td>{{ $usuario->rol->nombre }}</td>
                            <td class="whitespace-nowrap">{{ $usuario->created_at?->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
