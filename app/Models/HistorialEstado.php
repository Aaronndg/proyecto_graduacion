<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstado extends Model
{
    protected $table = 'historial_estado';
    protected $primaryKey = 'id_historial';
    public $timestamps = false;

    protected $fillable = ['id_estado', 'id_usuario', 'fecha_hora', 'observacion'];

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoPedido::class, 'id_estado', 'id_estado');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
