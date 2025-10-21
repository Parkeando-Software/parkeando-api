<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActivateAccountNotification extends Notification
{
    use Queueable;

    protected $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $activationUrl = config('app.frontend_url') . '/activate-account?token=' . $this->token;

        return (new MailMessage)
            ->subject('Activa tu cuenta - Parkeando')
            ->greeting("¡Hola {$notifiable->name}!")
            ->line('Gracias por registrarte. Para activar tu cuenta, haz clic en el botón de abajo.')
            ->action('Activar cuenta', $activationUrl)
            ->line('Si tú no creaste esta cuenta, ignora este mensaje.')
            ->salutation('¡Gracias por usar Parkeando!');
    }
}
