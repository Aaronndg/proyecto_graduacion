<?php

namespace App\Models\Concerns;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Aísla los datos de cada emprendimiento (RN-10): cuando el usuario autenticado es un
 * emprendedor, todas las consultas se limitan a sus propios registros y los nuevos
 * registros quedan asignados a él automáticamente.
 */
trait PerteneceAEmprendedor
{
    protected static function bootPerteneceAEmprendedor(): void
    {
        static::addGlobalScope('emprendedor', function (Builder $query) {
            $usuario = Auth::user();

            if ($usuario instanceof Usuario && $usuario->esEmprendedor()) {
                $query->where($query->getModel()->getTable().'.id_emprendedor', $usuario->id_usuario);
            }
        });

        static::creating(function ($modelo) {
            $usuario = Auth::user();

            if (! $modelo->id_emprendedor && $usuario instanceof Usuario && $usuario->esEmprendedor()) {
                $modelo->id_emprendedor = $usuario->id_usuario;
            }
        });
    }

    public function emprendedor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_emprendedor', 'id_usuario');
    }
}
