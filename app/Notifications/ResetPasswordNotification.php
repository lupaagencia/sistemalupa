<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $email = $notifiable->email;

        // Construct reset URL matching Laravel's route structure
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $email,
        ], false));

        return (new MailMessage)
            ->subject('Restablecer Contraseña - Empaques Lupa')
            ->greeting('¡Hola!')
            ->line('Recibiste este correo porque recibimos una solicitud de restablecimiento de contraseña para tu cuenta.')
            ->action('Restablecer Contraseña', $url)
            ->line('Este enlace de restablecimiento de contraseña expirará en 60 minutos.')
            ->line('Si no solicitaste este restablecimiento, no se requiere ninguna acción adicional.')
            ->salutation('Saludos, ' . config('app.name'));
    }
}
