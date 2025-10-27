<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactFormNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            // Forzar el remitente a la dirección configurada en mail.from (debe estar verificada en Brevo)
            ->from(config('mail.from.address'), config('mail.from.name'))
            // Establecer reply-to al correo del usuario para que las respuestas vayan a quien envió el formulario
            ->replyTo($this->data['email'], $this->data['name'])
            ->subject('Nuevo mensaje de contacto - ' . $this->data['subject'])
            ->greeting('¡Hola!')
            ->line('Has recibido un nuevo mensaje de contacto desde el formulario web.')
            ->line('Detalles del mensaje:')
            ->line('Nombre: ' . $this->data['name'])
            ->line('Email: ' . $this->data['email'])
            ->line('Asunto: ' . $this->data['subject'])
            ->line('Mensaje:')
            ->line($this->data['message'])
            ->line('Este mensaje fue enviado desde el formulario de contacto de ParKeando.');
    }
}