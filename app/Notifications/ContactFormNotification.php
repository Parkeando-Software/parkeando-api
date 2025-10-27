<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactFormNotification extends Notification
{
    // use Queueable; // Opcional, puedes dejarlo o eliminarlo

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Nuevo mensaje de contacto - ' . $this->data['subject'])
            ->line('Has recibido un nuevo mensaje de contacto desde el formulario web.')
            ->line('Nombre: ' . $this->data['name'])
            ->line('Email: ' . $this->data['email'])
            ->line('Asunto: ' . $this->data['subject'])
            ->line('Mensaje: ' . $this->data['message'])
            ->line('Este mensaje fue enviado desde el formulario de contacto de ParKeando.');

        if (filter_var($this->data['email'], FILTER_VALIDATE_EMAIL)) {
            $mail->replyTo($this->data['email'], $this->data['name']);
        }

        return $mail;
    }
}
