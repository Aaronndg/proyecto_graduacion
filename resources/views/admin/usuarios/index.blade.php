<x-layouts.app titulo="Usuarios" subtitulo="Cuentas con acceso a la plataforma.">
    <x-slot:acciones>
        <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primario"><x-icono nombre="mas" clase="size-4" /> Nuevo usuario</a>
    </x-slot:acciones>

    <div class="tarjeta overflow-hidden">
        <form method="GET" class="flex flex-col gap-2 border-b border-stone-100 p-4 sm:flex-row" role="search">
            <x-campo-busqueda :valor="$buscar" placeholder="Nombre, correo o negocio" />
            <label for="rol" class="sr-only">Rol</label>
            <select id="rol" name="rol" class="campo sm:w-44" onchange="this.form.submit()">
                <option value="">Todos los roles</option>
                @foreach ($roles as $opcion)
                    <option value="{{ $opcion->id_rol }}" @selected($rol === $opcion->id_rol)>{{ $opcion->nombre }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secundario">Buscar</button>
        </form>

        <div class="overflow-x-auto">
            <table class="tabla">
                <thead>
                    <tr><th>Usuario</th><th>Rol</th><th class="hidden md:table-cell">Negocio</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($usuarios as $usuario)
                        <tr>
                            <td>
                                <p class="font-medium text-stone-900">{{ $usuario->nombre }}</p>
                                <p class="text-xs text-stone-500">{{ $usuario->correo }}</p>
                            </td>
                            <td>{{ $usuario->rol->nombre }}</td>
                            <td class="hidden text-stone-600 md:table-cell">{{ $usuario->negocio ?? '—' }}</td>
                            <td>
                                <span @class(['insignia', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $usuario->activo, 'bg-stone-100 text-stone-500 ring-stone-500/20' => ! $usuario->activo])>
                                    {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-secundario px-3 py-1.5">Editar</a>
                                    @unless ($usuario->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.usuarios.estado', $usuario) }}"
                                              data-confirmar-titulo="{{ $usuario->activo ? '¿Desactivar la cuenta de '.$usuario->nombre.'?' : '¿Activar la cuenta de '.$usuario->nombre.'?' }}"
                                              data-confirmar="{{ $usuario->activo ? 'No podrá iniciar sesión hasta que la active de nuevo. Sus datos se conservan.' : 'Podrá volver a iniciar sesión.' }}"
                                              data-confirmar-accion="{{ $usuario->activo ? 'Desactivar cuenta' : 'Activar cuenta' }}" @if ($usuario->activo) data-confirmar-peligro @endif>
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
                        <tr><td colspan="5" class="py-12 text-center text-stone-500">No encontramos usuarios con esos datos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($usuarios->hasPages())<div class="border-t border-stone-100 px-5 py-3">{{ $usuarios->links() }}</div>@endif
    </div>
</x-layouts.app>
