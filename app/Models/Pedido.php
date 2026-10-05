<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmprendedor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    use PerteneceAEmprendedor;

    protected $table = 'pedidos';
    protected $primaryKey = 'id_pedido';

    protected $fillable = ['fecha', 'fecha_entrega', 'total', 'id_cliente', 'id_estado'];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'fecha_entrega' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoPedido::class, 'id_estado', 'id_estado');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido', 'id_pedido');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialEstado::class, 'id_pedido', 'id_pedido')->orderBy('fecha_hora')->orderBy('id_historial');
    }

    public function numero(): string
    {
        return str_pad((string) $this->id_pedido, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Cómo se lee la fecha de entrega: «Para hoy», «Para mañana», «Para el jue 8 oct.» o «Atrasado»
     * (solo mientras el pedido está activo). tono: atrasado | hoy | pronto | neutro.
     */
    public function entrega(): ?array
    {
        if (! $this->fecha_entrega) {
            return null;
        }

        $dia = $this->fecha_entrega;
        if (in_array($this->id_estado, EstadoPedido::FINALES, true)) {
            return ['texto' => 'Entrega: '.$dia->translatedFormat('j M'), 'tono' => 'neutro'];
        }

        return match (true) {
            $dia->isToday() => ['texto' => 'Para hoy', 'tono' => 'hoy'],
            $dia->isPast() => ['texto' => 'Atrasado: era para el '.$dia->translatedFormat('j M'), 'tono' => 'atrasado'],
            $dia->isTomorrow() => ['texto' => 'Para mañana', 'tono' => 'pronto'],
            default => ['texto' => 'Para el '.$dia->translatedFormat('D j M'), 'tono' => 'pronto'],
        };
    }
}
