<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmprendedor;
use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory, PerteneceAEmprendedor;

    protected $table = 'productos';
    protected $primaryKey = 'id_producto';

    protected $fillable = ['nombre', 'descripcion', 'precio', 'estado'];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'estado' => 'boolean',
        ];
    }

    public function scopeActivos(Builder $query): void
    {
        $query->where('estado', true);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'id_producto', 'id_producto');
    }
}
