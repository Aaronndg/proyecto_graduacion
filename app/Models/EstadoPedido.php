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

    /** Estados en los que el pedido todavía necesita atención (las columnas de «Hoy»). */
    public const ACTIVOS = [self::NUEVO, self::EN_PROCESO, self::LISTO];

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

    /** Frase en lenguaje sencillo que ve el cliente sobre su pedido. */
    public function mensajeCliente(): string
    {
        return match ($this->id_estado) {
            self::NUEVO => 'Recibimos su pedido',
            self::EN_PROCESO => 'Su pedido se está preparando',
            self::LISTO => 'Su pedido está listo',
            self::ENTREGADO => 'Su pedido fue entregado',
            self::CANCELADO => 'Este pedido fue cancelado',
            default => $this->nombre,
        };
    }
}
