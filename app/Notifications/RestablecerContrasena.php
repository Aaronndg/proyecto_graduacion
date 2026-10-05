<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Correo con el enlace para crear una contraseña nueva, en palabras sencillas. */
class RestablecerContrasena extends Notification
{
    public function __construct(private readonly string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $enlace = route('contrasena.nueva', ['token' => $this->token, 'correo' => $notifiable->correo]);
        $minutos = config('auth.passwords.users.expire');

        return (new MailMessage)
            ->subject('Cree su contraseña nueva de NEXO')
            ->greeting('Hola, '.strtok($notifiable->nombre, ' '))
            ->line('Nos pidió ayuda para entrar a NEXO. Toque el botón para crear una contraseña nueva.')
            ->action('Crear contraseña nueva', $enlace)
            ->line("El botón funciona durante {$minutos} minutos.")
            ->line('Si usted no lo pidió, no haga nada: su contraseña sigue igual.')
            ->salutation('Saludos, NEXO');
    }
}
