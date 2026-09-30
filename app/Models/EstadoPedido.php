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

    /** Estados que forman el avance normal del pedido (la barra de progreso). */
    public const FLUJO = [self::NUEVO, self::EN_PROCESO, self::LISTO, self::ENTREGADO];

    public function esFinal(): bool
    {
        return in_array($this->id_estado, self::FINALES, true);
    }

    /** Texto que se guarda en el historial cuando no se escribe una observación. */
    public function observacionPredeterminada(): string
    {
        return match ($this->id_estado) {
            self::NUEVO => 'Pedido registrado en el sistema.',
            self::EN_PROCESO => 'Pedido en preparación.',
            self::LISTO => 'Pedido listo para entrega.',
            self::ENTREGADO => 'Pedido entregado al cliente.',
            self::CANCELADO => 'Pedido cancelado.',
            default => 'Cambio de estado.',
        };
    }

    /** Texto del botón para avanzar a este estado. */
    public function accion(): string
    {
        return match ($this->id_estado) {
            self::EN_PROCESO => 'Iniciar preparación',
            self::LISTO => 'Marcar como listo',
            self::ENTREGADO => 'Marcar como entregado',
            self::CANCELADO => 'Cancelar pedido',
            default => $this->nombre,
        };
    }

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
