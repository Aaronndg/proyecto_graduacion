<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    public const ADMINISTRADOR = 1;
    public const EMPRENDEDOR = 2;
    public const CLIENTE = 3;

    protected $table = 'roles';
    protected $primaryKey = 'id_rol';
    public $timestamps = false;

    protected $fillable = ['id_rol', 'nombre', 'descripcion'];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'id_rol', 'id_rol');
    }
}
