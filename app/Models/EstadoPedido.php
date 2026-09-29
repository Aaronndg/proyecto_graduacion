<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoPedido extends Model
{
    public const NUEVO = 1;
    public const EN_PROCESO = 2;
    public const LISTO = 3;
    public const ENTREGADO = 4;
    public const CANCELADO = 5;

    /** Estados en los que el pedido ya no puede cambiar. */
    public const FINALES = [self::ENTREGADO, self::CANCELADO];

    protected $table = 'estados_pedido';
    protected $primaryKey = 'id_estado';
    public $timestamps = false;

    protected $fillable = ['id_estado', 'nombre', 'descripcion', 'orden'];

    /** Clase de color para la etiqueta del estado (siempre acompañada del nombre, 5.5.4). */
    public function color(): string
    {
        return match ($this->id_estado) {
            self::NUEVO => 'bg-blue-50 text-blue-700 ring-blue-600/20',
            self::EN_PROCESO => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            self::LISTO => 'bg-violet-50 text-violet-700 ring-violet-600/20',
            self::ENTREGADO => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            self::CANCELADO => 'bg-slate-100 text-slate-600 ring-slate-500/20',
            default => 'bg-slate-100 text-slate-600 ring-slate-500/20',
        };
    }
}
