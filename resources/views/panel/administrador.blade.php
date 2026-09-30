<x-layouts.app :titulo="'Hola, '.strtok(auth()->user()->nombre, ' ')" subtitulo="Resumen general de la plataforma.">
    <x-slot:acciones>
        <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Nuevo usuario</a>
    </x-slot:acciones>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-estadistica titulo="Emprendedores" :valor="$totalEmprendedores" icono="negocio" :enlace="route('admin.usuarios.index', ['rol' => \App\Models\Rol::EMPRENDEDOR])" />
        <x-estadistica titulo="Clientes con cuenta" :valor="$totalClientes" icono="usuarios" color="bg-sky-50 text-sky-600" :enlace="route('admin.usuarios.index', ['rol' => \App\Models\Rol::CLIENTE])" />
        <x-estadistica titulo="Pedidos registrados" :valor="$totalPedidos" icono="pedido" color="bg-amber-50 text-amber-600" />
        <x-estadistica titulo="Cuentas desactivadas" :valor="$totalInactivos" icono="alerta" color="bg-stone-100 text-stone-500" />
    </div>

    <section class="tarjeta mt-6 overflow-hidden">
        <div class="flex items-center justify-between px-5 pt-5 pb-3">
            <h2 class="font-semibold text-stone-900">Usuarios recientes</h2>
            <a href="{{ route('admin.usuarios.index') }}" class="enlace text-sm">Ver todos</a>
        </div>
        <div class="overflow-x-auto">
            <table class="tabla">
                <thead>
                    <tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Registro</th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($usuariosRecientes as $usuario)
                        <tr>
                            <td class="font-medium text-stone-900">{{ $usuario->nombre }}</td>
                            <td class="text-stone-500">{{ $usuario->correo }}</td>
                            <td>{{ $usuario->rol->nombre }}</td>
                            <td class="whitespace-nowrap text-stone-500">{{ $usuario->created_at?->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
