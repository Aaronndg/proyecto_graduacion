<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAEmprendedor;
use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

    /** Dirección pública de la foto, o null si el producto no tiene. */
    public function urlImagen(): ?string
    {
        return $this->imagen ? asset('storage/'.$this->imagen) : null;
    }

    /** Guarda la nueva foto (o la quita) y borra el archivo anterior para no dejar basura en el disco. */
    public function cambiarImagen(?UploadedFile $archivo, bool $quitar = false): void
    {
        if (! $archivo && ! $quitar) {
            return;
        }

        $anterior = $this->imagen;
        $this->imagen = $archivo?->store('productos', 'public');

        if ($anterior) {
            Storage::disk('public')->delete($anterior);
        }
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
