<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmprendedor;
use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory, PerteneceAEmprendedor;

    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';

    protected $fillable = ['nombre', 'telefono', 'correo', 'direccion'];

    protected static function booted(): void
    {
        // Si ya existe una cuenta de cliente con este correo, se vincula el registro.
        static::saving(function (Cliente $cliente) {
            if ($cliente->isDirty('correo')) {
                $cliente->id_usuario = $cliente->correo
                    ? Usuario::where('correo', $cliente->correo)->where('id_rol', Rol::CLIENTE)->value('id_usuario')
                    : null;
            }
        });
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_cliente', 'id_cliente');
    }
}
