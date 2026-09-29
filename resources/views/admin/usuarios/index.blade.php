<x-layouts.app titulo="Gestión de usuarios">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <form method="GET" class="flex flex-1 flex-col gap-2 sm:flex-row sm:items-end" role="search">
            <div class="flex-1 sm:max-w-xs">
                <label for="buscar" class="etiqueta">Buscar</label>
                <input id="buscar" name="buscar" value="{{ $buscar }}" placeholder="Nombre, correo o negocio" class="campo">
            </div>
            <div>
                <label for="rol" class="etiqueta">Rol</label>
                <select id="rol" name="rol" class="campo">
                    <option value="">Todos</option>
                    @foreach ($roles as $opcion)
                        <option value="{{ $opcion->id_rol }}" @selected($rol === $opcion->id_rol)>{{ $opcion->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-secundario"><x-icono nombre="buscar" clase="size-4" /> Consultar</button>
        </form>

        <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Nuevo usuario</a>
    </div>

    <div class="tarjeta overflow-hidden">
        <div class="overflow-x-auto">
            <table class="tabla">
                <thead>
                    <tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Negocio</th><th>Estado</th><th class="text-right">Acciones</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($usuarios as $usuario)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $usuario->nombre }}</td>
                            <td>{{ $usuario->correo }}</td>
                            <td>{{ $usuario->rol->nombre }}</td>
                            <td>{{ $usuario->negocio ?? '—' }}</td>
                            <td>
                                <span @class(['insignia', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $usuario->activo, 'bg-slate-100 text-slate-600 ring-slate-500/20' => ! $usuario->activo])>
                                    {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-secundario px-3 py-1.5"><x-icono nombre="editar" clase="size-4" /> Editar</a>
                                    @unless ($usuario->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.usuarios.estado', $usuario) }}"
                                              data-confirmar="{{ $usuario->activo ? '¿Desactivar la cuenta de '.$usuario->nombre.'? No podrá iniciar sesión.' : '¿Activar la cuenta de '.$usuario->nombre.'?' }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="{{ $usuario->activo ? 'btn btn-peligro' : 'btn btn-secundario' }} px-3 py-1.5">
                                                {{ $usuario->activo ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-slate-500">No se encontraron usuarios con esos criterios.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $usuarios->links() }}</div>
</x-layouts.app>
