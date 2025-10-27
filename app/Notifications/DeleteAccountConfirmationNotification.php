<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\DeleteRequest;

class DeleteAccountConfirmationNotification extends Notification
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
        $confirmationUrl = config('app.frontend_url') . '/account/delete-request/confirm/' . $this->deleteRequest->confirmation_token;
        $cancellationUrl = config('app.frontend_url') . '/account/delete-request/cancel/' . $this->deleteRequest->confirmation_token;
        
        $reasonText = DeleteRequest::REASONS[$this->deleteRequest->reason] ?? $this->deleteRequest->reason;

        return (new MailMessage)
            ->subject('Confirmación de eliminación de cuenta - Parkeando')
            ->greeting("¡Hola {$notifiable->name}!")
            ->line('Hemos recibido tu solicitud de eliminación de cuenta y datos personales.')
            ->line("**Motivo:** {$reasonText}")
            ->when($this->deleteRequest->additional_info, function ($message) {
                return $message->line("**Información adicional:** {$this->deleteRequest->additional_info}");
            })
            ->line('**IMPORTANTE:** Para proceder con la eliminación, debes confirmar tu solicitud haciendo clic en el botón de abajo.')
            ->line('Una vez confirmada, tu cuenta será eliminada en **30 días**. Durante este período podrás cancelar la solicitud.')
            ->action('Confirmar eliminación', $confirmationUrl)
            ->line('Si cambias de opinión, puedes cancelar la solicitud:')
            ->action('Cancelar solicitud', $cancellationUrl)
            ->line('**¿Qué se eliminará?**')
            ->line('• Tu cuenta de usuario')
            ->line('• Todos tus datos personales')
            ->line('• Historial de aparcamiento')
            ->line('• Vehículos registrados')
            ->line('• Notificaciones y preferencias')
            ->line('Si tú no solicitaste esta eliminación, ignora este mensaje.')
            ->salutation('Gracias por usar Parkeando');
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
            'reason' => $this->deleteRequest->reason,
            'confirmation_token' => $this->deleteRequest->confirmation_token,
        ];
    }
}
