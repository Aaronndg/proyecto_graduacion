<?php

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\RestablecerContrasena;
use App\Support\WhatsApp;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    protected $fillable = ['nombre', 'correo', 'contrasena', 'id_rol', 'negocio', 'telefono', 'activo'];

    protected $hidden = ['contrasena', 'remember_token'];

    protected function casts(): array
    {
        return [
            'contrasena' => 'hashed',
            'activo' => 'boolean',
            'id_rol' => 'integer',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    /** Nombre que ven los clientes: el del negocio o, si no tiene, el de la persona. */
    public function nombreNegocio(): string
    {
        return $this->negocio ?: $this->nombre;
    }

    public function urlLogo(): ?string
    {
        $logo = $this->attributes['logo'] ?? null;

        return $logo ? asset('storage/'.$logo) : null;
    }

    /** Guarda el logo nuevo (o lo quita) y borra el anterior. */
    public function cambiarLogo(?UploadedFile $archivo, bool $quitar = false): void
    {
        if (! $archivo && ! $quitar) {
            return;
        }

        $anterior = $this->attributes['logo'] ?? null;
        $this->logo = $archivo?->store('logos', 'public');

        if ($anterior) {
            Storage::disk('public')->delete($anterior);
        }
    }

    /** Dirección pública del catálogo; la primera vez se crea a partir del nombre del negocio (dulces-maria, dulces-maria-2…). */
    public function enlaceCatalogo(): string
    {
        if (! ($this->attributes['catalogo'] ?? null)) {
            $base = Str::slug($this->nombreNegocio()) ?: 'negocio';
            $slug = $base;
            for ($n = 2; static::where('catalogo', $slug)->exists(); $n++) {
                $slug = $base.'-'.$n;
            }
            $this->forceFill(['catalogo' => $slug])->save();
        }

        return route('catalogo', $this->catalogo);
    }

    /** WhatsApp del negocio (o null), sin fallar si el modelo se cargó sin esa columna. */
    public function telefonoNegocio(): ?string
    {
        return $this->attributes['telefono'] ?? null;
    }

    /** Enlace para que un cliente le escriba al negocio por WhatsApp, con un saludo ya escrito. */
    public function enlaceWhatsAppNegocio(string $mensaje): ?string
    {
        return WhatsApp::enlace($this->attributes['telefono'] ?? null, $mensaje);
    }

    /** «¿Olvidó su contraseña?»: el correo de la cuenta está en la columna «correo». */
    public function getEmailForPasswordReset(): string
    {
        return $this->correo;
    }

    public function routeNotificationForMail(): string
    {
        return $this->correo;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new RestablecerContrasena($token));
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
