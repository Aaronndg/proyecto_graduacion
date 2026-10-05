<?php

namespace App\Support;

/** Enlaces de WhatsApp (wa.me) con el mensaje ya escrito. */
class WhatsApp
{
    /** Número para wa.me: solo dígitos y con 502 si es un número de Guatemala de 8 dígitos; null si no es válido. */
    public static function numero(?string $telefono): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $telefono);

        if (strlen($digitos) < 8) {
            return null;
        }

        return strlen($digitos) === 8 ? '502'.$digitos : $digitos;
    }

    public static function enlace(?string $telefono, string $mensaje): ?string
    {
        $numero = self::numero($telefono);

        return $numero ? 'https://wa.me/'.$numero.'?text='.rawurlencode($mensaje) : null;
    }
}
