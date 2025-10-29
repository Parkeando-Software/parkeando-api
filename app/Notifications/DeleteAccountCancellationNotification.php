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
            ->view('emails.delete-cancelled', [
                'username' => $notifiable->username,
                'deleteRequest' => $this->deleteRequest,
            ]);
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
