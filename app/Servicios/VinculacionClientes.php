<?php

namespace App\Servicios;

use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * RN-06: la cuenta de un cliente solo accede a los pedidos de los registros que vinculó con el
 * código entregado por el emprendedor. Los intentos fallidos se limitan para impedir adivinar códigos.
 */
class VinculacionClientes
{
    private const INTENTOS_MAXIMOS = 5;
    private const BLOQUEO_SEGUNDOS = 600;

    public function vincular(Usuario $usuario, string $codigo): Cliente
    {
        $cliente = $this->buscar($codigo, 'vincular-cliente:'.$usuario->id_usuario);
        $cliente->vincularCon($usuario);

        return $cliente;
    }

    /**
     * Devuelve el registro de cliente que corresponde a un código válido y sin usar.
     * Debe llamarse fuera de transacciones para que los intentos fallidos queden contados.
     *
     * @param  string  $claveLimite  Identifica a quien intenta (la cuenta o, en el registro, la IP).
     */
    public function buscar(string $codigo, string $claveLimite, string $campo = 'codigo'): Cliente
    {
        if (RateLimiter::tooManyAttempts($claveLimite, self::INTENTOS_MAXIMOS)) {
            $minutos = (int) ceil(RateLimiter::availableIn($claveLimite) / 60);

            throw ValidationException::withMessages([
                $campo => "Demasiados intentos con códigos incorrectos. Intente de nuevo en {$minutos} minuto(s).",
            ]);
        }

        $cliente = Cliente::withoutGlobalScopes()
            ->with('emprendedor')
            ->where('codigo_vinculacion', Cliente::normalizarCodigo($codigo))
            ->whereNull('id_usuario')
            ->first();

        if (! $cliente) {
            RateLimiter::hit($claveLimite, self::BLOQUEO_SEGUNDOS);

            throw ValidationException::withMessages([
                $campo => 'El código no es válido o ya fue utilizado. Verifíquelo con el negocio que se lo envió.',
            ]);
        }

        RateLimiter::clear($claveLimite);

        return $cliente;
    }
}
