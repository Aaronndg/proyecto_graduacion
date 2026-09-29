<?php

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    protected $fillable = ['nombre', 'correo', 'contrasena', 'id_rol', 'negocio', 'activo'];

    protected $hidden = ['contrasena', 'remember_token'];

    protected function casts(): array
    {
        return [
            'contrasena' => 'hashed',
            'activo' => 'boolean',
            'id_rol' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Al crear una cuenta de cliente, se vincula con los registros de cliente que
        // los emprendedores hayan creado con el mismo correo (RN-06: consulta de sus pedidos).
        static::created(function (Usuario $usuario) {
            if ($usuario->esCliente()) {
                Cliente::withoutGlobalScopes()
                    ->whereNull('id_usuario')
                    ->where('correo', $usuario->correo)
                    ->update(['id_usuario' => $usuario->id_usuario]);
            }
        });
    }

    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    /** Registros de cliente vinculados a esta cuenta (uno por cada emprendedor). */
    public function registrosCliente(): HasMany
    {
        return $this->hasMany(Cliente::class, 'id_usuario', 'id_usuario');
    }

    public function esAdministrador(): bool
    {
        return $this->id_rol === Rol::ADMINISTRADOR;
    }

    public function esEmprendedor(): bool
    {
        return $this->id_rol === Rol::EMPRENDEDOR;
    }

    public function esCliente(): bool
    {
        return $this->id_rol === Rol::CLIENTE;
    }

    public function tieneRol(string ...$roles): bool
    {
        $mapa = ['administrador' => Rol::ADMINISTRADOR, 'emprendedor' => Rol::EMPRENDEDOR, 'cliente' => Rol::CLIENTE];

        foreach ($roles as $rol) {
            if (($mapa[$rol] ?? null) === $this->id_rol) {
                return true;
            }
        }

        return false;
    }

    public function iniciales(): string
    {
        return collect(explode(' ', trim($this->nombre)))
            ->filter()
            ->take(2)
            ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
            ->implode('');
    }
}
