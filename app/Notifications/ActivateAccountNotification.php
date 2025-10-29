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
            ->view('emails.activate-account', [
                'username' => $notifiable->username,
                'activationUrl' => $activationUrl,
            ]);
    }
}
