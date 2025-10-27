<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\DeleteRequest;

class DeleteAccountCancellationNotification extends Notification
{
    use Queueable;

    protected $deleteRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(DeleteRequest $deleteRequest)
    {
        $this->deleteRequest = $deleteRequest;
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
            ->subject('Solicitud de eliminación cancelada - Parkeando')
            ->greeting("¡Hola {$notifiable->name}!")
            ->line('Tu solicitud de eliminación de cuenta ha sido **cancelada exitosamente**.')
            ->line('Tu cuenta y todos tus datos permanecen intactos.')
            ->line('**Detalles de la solicitud cancelada:**')
            ->line("• **ID de solicitud:** {$this->deleteRequest->id}")
            ->line("• **Fecha de cancelación:** {$this->deleteRequest->cancelled_at->format('d/m/Y H:i')}")
            ->line('**¿Qué significa esto?**')
            ->line('• Tu cuenta sigue activa y funcional')
            ->line('• Todos tus datos están seguros')
            ->line('• Puedes seguir usando Parkeando normalmente')
            ->line('• Si cambias de opinión en el futuro, puedes solicitar la eliminación nuevamente')
            ->line('Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.')
            ->salutation('¡Gracias por seguir con nosotros!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->deleteRequest->id,
            'cancelled_at' => $this->deleteRequest->cancelled_at,
        ];
    }
}
