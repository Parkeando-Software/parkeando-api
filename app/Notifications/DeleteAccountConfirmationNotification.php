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

        $reasonText = \App\Models\DeleteRequest::REASONS[$this->deleteRequest->reason] ?? $this->deleteRequest->reason;

        return (new MailMessage)
            ->subject('Confirmación de eliminación de cuenta - Parkeando')
            ->view('emails.delete-confirmation', [
                'username' => $notifiable->username,
                'confirmationUrl' => $confirmationUrl,
                'cancellationUrl' => $cancellationUrl,
                'reasonText' => $reasonText,
                'additionalInfo' => $this->deleteRequest->additional_info,
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
            'reason' => $this->deleteRequest->reason,
            'confirmation_token' => $this->deleteRequest->confirmation_token,
        ];
    }
}
