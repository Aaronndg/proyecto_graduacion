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

    protected $fillable = ['fecha', 'total', 'id_cliente', 'id_estado'];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
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
}
