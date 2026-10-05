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

    /** Sin 0/O, 1/I/L para evitar confusiones al dictar o escribir el código. */
    private const ALFABETO_CODIGO = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';

    protected $fillable = ['nombre', 'telefono', 'correo', 'direccion'];

    protected static function booted(): void
    {
        static::creating(function (Cliente $cliente) {
            if (! $cliente->id_usuario && ! $cliente->codigo_vinculacion) {
                $cliente->codigo_vinculacion = self::generarCodigo();
            }
        });
    }

    public static function generarCodigo(): string
    {
        do {
            $codigo = '';
            for ($i = 0; $i < 8; $i++) {
                $codigo .= self::ALFABETO_CODIGO[random_int(0, strlen(self::ALFABETO_CODIGO) - 1)];
            }
        } while (static::withoutGlobalScopes()->where('codigo_vinculacion', $codigo)->exists());

        return $codigo;
    }

    /** Acepta el código con o sin guion, en mayúsculas o minúsculas. */
    public static function normalizarCodigo(string $codigo): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $codigo));
    }

    /** Código para mostrar: K7QM-4XPA */
    public function codigoFormateado(): ?string
    {
        return $this->codigo_vinculacion
            ? substr($this->codigo_vinculacion, 0, 4).'-'.substr($this->codigo_vinculacion, 4)
            : null;
    }

    /** Vincula este registro con la cuenta del cliente e invalida el código (uso único). */
    public function vincularCon(Usuario $usuario): void
    {
        $this->id_usuario = $usuario->id_usuario;
        $this->codigo_vinculacion = null;
        $this->save();
    }

    /** Quita la cuenta vinculada y emite un código nuevo. */
    public function regenerarCodigo(): void
    {
        $this->id_usuario = null;
        $this->codigo_vinculacion = self::generarCodigo();
        $this->save();
    }

    /** Enlace de WhatsApp con el mensaje de invitación listo para enviar (Guatemala: +502). */
    /** Número para wa.me (con 502 si es un número de Guatemala de 8 dígitos), o null si no tiene uno válido. */
    public function numeroWhatsApp(): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $this->telefono);

        if (strlen($digitos) < 8) {
            return null;
        }

        return strlen($digitos) === 8 ? '502'.$digitos : $digitos;
    }

    /** Mensaje de WhatsApp, ya escrito, para avisar que el pedido está listo. Palabras sencillas, sin datos técnicos. */
    public function avisoPedidoListo(Pedido $pedido, string $negocio): ?string
    {
        if (! $numero = $this->numeroWhatsApp()) {
            return null;
        }

        $mensaje = 'Hola '.strtok($this->nombre, ' ').", le saluda {$negocio}. ¡Su pedido ya está listo! "
            .'Pedido #'.$pedido->numero().' por Q '.number_format((float) $pedido->total, 2).'.';

        if ($this->id_usuario) {
            $mensaje .= ' Puede verlo aquí: '.route('mis-pedidos.show', $pedido);
        }

        return 'https://wa.me/'.$numero.'?text='.rawurlencode($mensaje.' ¡Gracias por su compra!');
    }

    public function enlaceWhatsApp(string $negocio): ?string
    {
        $digitos = $this->numeroWhatsApp();

        if (! $this->codigo_vinculacion || ! $digitos) {
            return null;
        }

        $mensaje = "Hola {$this->nombre}, en {$negocio} ya puede consultar el estado de sus pedidos en línea. "
            ."Cree su cuenta aquí: ".route('registro', ['codigo' => $this->codigoFormateado()])
            ." (su código es {$this->codigoFormateado()}).";

        return 'https://wa.me/'.$digitos.'?text='.rawurlencode($mensaje);
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
